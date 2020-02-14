<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arResult['FORM_NAME'] = $arParams['FORM_NAME'] ? $arParams['FORM_NAME'] : 'EXCEL_BASKET_FORM';
$arResult['FILE_INPUT_NAME'] = 'EXCEL_FILE';
$removeOldProducts = $arParams['REMOVE_OLD_PRODUCTS'] == 'Y';
if($_POST['REMOVE_OLD_PRODUCTS'])
	$removeOldProducts = $_POST['REMOVE_OLD_PRODUCTS'] == 'Y';

$redirectAfterAdd = $arParams['REDIRECT_AFTER_ADD'] == 'Y';
$conditions = \Bitrix\Main\Web\Json::decode(base64_decode($arParams['CONDITIONS']));


if($conditions)
	$conditions = prepareConditions($conditions);

foreach ($conditions['USERTYPES'] as $key => $arType) {
	if($arType['USERTYPES_SYMBOL_COLUMN'] == $arParams['USERTYPE_SYMBOL_CODE']){
		if($arType['USERTYPES_PRICE_COLUMN'])
			$conditions['PRICE_COLUMN'] = $arType['USERTYPES_PRICE_COLUMN'];
		if($arType['USERTYPES_QUANT_COLUMN'])
			$conditions['QUANT_COLUMN'] = $arType['USERTYPES_QUANT_COLUMN'];
		break;
	}
}

$uniqCode = $arParams['UNIQ_CODE'];
$iblockId = IntVal($arParams['IBLOCK_ID']);
$basketUrl = $arParams['BASKET_URL'] ? $arParams['BASKET_URL'] : '/basket/';
$skipInactiveProducts = $arParams['SKIP_INACTIVE_PRODUCTS'] == 'Y';



// /vars
function prepareConditions($conditions){
	$conditions['PRICE_COLUMN'] = 'A';
	$conditions['QUANT_COLUMN'] = 'B';
	foreach ($conditions as $key => $condition) {
		if(stripos($key, 'USERTYPES_') !== false){
			$number = intVal(preg_replace('/[^0-9]/','', $key));
			if(!$number)
				continue;

			$name = preg_replace('/_[0-9]/','', $key);
			$conditions['USERTYPES'][$number][$name] = $condition;
			unset($conditions[$key]);
		}
	}
	return $conditions;
}


function checkValue($condition, $value){
	switch ($condition) {
		case 'NOT_EMPTY':
			if(!empty($value))
				return true;
			break;
		case 'IS_NUMBER':
			if(floatval($value))
				return true;
			break;
		case 'IS_EMPTY':
			if(empty($value))
				return true;
			break;
		case 'NOT_NUMBER':
			if(!floatval($value))
				return true;
			break;
	}
	return false;
}

function checkConditions($conditions, $value){
	if(is_array($conditions)){
		foreach ($conditions as $condition) {
			if(!checkValue($condition, $value))
				return false;
		}
		return true;
	}else{
		return checkValue($conditions, $value);
	}
	return false;
}

if($_POST['SUBMIT_'.$arResult['FORM_NAME']] == "Y" && $_SERVER['REQUEST_METHOD'] == "POST"){
	// AddMessage2Log($_POST);
	// AddMessage2Log($_FILES);

	// first save the file to open it later
	$file = reset($_FILES);
	$arIMAGE = array(
		'name' => $file['name'],
		'size' => $file['size'],
		'tmp_name' => $file['tmp_name'],
		'type' => $file['type']
	);
	$fid = CFile::SaveFile($arIMAGE, 'excel_basket');
	if($fid){
		$io = new CBXVirtualIo;
		$filePath = CFile::GetPath($fid);
		$absoluteFilePath = $io->RelativeToAbsolutePath($filePath);
		if($io->FileExists($absoluteFilePath)){
			include('get_file.php');
			$fileData = fileData::getFileData($absoluteFilePath);
			// var_dump($fileData);
			if($fileData['SUCCESS']){

				//find products
				$products = array();
				$uniqValues = array();
				foreach ($fileData['DATA']['ROWS'] as $rowNum => $row) {
					$product = array();
					foreach ($row as $cellKey => $cell) {
						$cellName = $cell['name'];
						$cellLetter = preg_replace('/[0-9]/','',$cellName);

						if($cellLetter == $conditions['PRODUCT_COLUMN'] && checkConditions($conditions['PRODUCT_CONDITION'], $cell['value']))
							$product['IS_PRODUCT'] = true;

						if($cellLetter == $conditions['UNIQ_COLUMN']){
							$product['UNIQ'] = $cell['value'];
						}

						if($cellLetter == $conditions['PRICE_COLUMN'])
							$product['PRICE'] = $cell['value'];

						if($cellLetter == $conditions['QUANT_COLUMN'])
							$product['QUANT'] = $cell['value'];
					}

					// fill array if its truly a product
					if($product['IS_PRODUCT'] && $product['QUANT'] > 0){
						$products[$product['UNIQ']] = $product;
						$uniqValues[] = $product['UNIQ'];
					}
				}

				if($products){
					// find products in iblock
					$arSelect = Array("ID", "NAME", "ACTIVE", $uniqCode, 'DETAIL_PAGE_URL');
					$arFilter = Array("IBLOCK_ID"=>$iblockId, "ACTIVE"=>"", $uniqCode => $uniqValues);
					$res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
					if($res->SelectedRowsCount() > 0){

						// we found products
						if($removeOldProducts && CModule::IncludeModule("sale")){
							// clean basket if need
							$basketRes = CSaleBasket::GetList(array(), array(
								'FUSER_ID' => CSaleBasket::GetBasketUserID(),
								'LID' => SITE_ID,
								'ORDER_ID' => 'null',
								'DELAY' => 'N',
								'CAN_BUY' => 'Y'
							));
							while ($basketRow = $basketRes->fetch()) {
								CSaleBasket::Delete($basketRow['ID']);
							}
						}


						while($ob = $res->GetNextElement()){
							$arFields = $ob->GetFields();

							if($skipInactiveProducts && $arFields['ACTIVE'] != 'Y')
								continue;

							// check uniq value
							if($arFields[$uniqCode]) //just field
								$uniqValue = $arFields[$uniqCode];
							elseif ($arFields[$uniqCode.'_VALUE']) //prop
								$uniqValue = $arFields[$uniqCode.'_VALUE'];
							else
								$uniqValue = false;

							//associate with file products
							if($fileProduct = $products[$uniqValue]){
								$arFields['QUANT'] = $fileProduct['QUANT'];
								$arFields['PRICE'] = $fileProduct['PRICE'];
								if (CModule::IncludeModule("catalog")){
									// check avail quant of product
									$catalogProd = CCatalogProduct::GetByID($arFields['ID']);
									if($catalogProd['QUANTITY'] == 0){
										$arResult['ADD_INFO']['NOT_AVAILABLE'][] = array(
											'DETAIL_PAGE_URL' => $arFields['DETAIL_PAGE_URL'],
											'ID' => $arFields['ID'],
											'NAME' => $arFields['NAME'],
											'UNIQ' => $uniqValue,
											'QUANT_NEED' => $arFields['QUANT'],
											'QUANT' => $catalogProd['QUANTITY']
										);
										continue;
									}else if($catalogProd['QUANTITY'] < $arFields['QUANT']){
										$arResult['ADD_INFO']['PARTIAL'][] = array(
											'DETAIL_PAGE_URL' => $arFields['DETAIL_PAGE_URL'],
											'ID' => $arFields['ID'],
											'NAME' => $arFields['NAME'],
											'UNIQ' => $uniqValue,
											'QUANT_NEED' => $arFields['QUANT'],
											'QUANT' => $catalogProd['QUANTITY']
										);
										$arFields['QUANT'] = $catalogProd['QUANTITY'];
									}



									$addRes = Add2BasketByProductID($arFields['ID'], $arFields['QUANT']);
									if(!$addRes)
										$arResult['ERROR'][] = GetMessage('PARTIAL_NOT_ADD');
									else
										$arResult['SUCCESS']['ADDED'] = GetMessage('PRODUCTS_ADDED_TO_CART');
								}
							}else{
								$arResult['ERROR'][] = GetMessage('NOT_FOUND_PRODUCTS_IN_FILE');
							}
						}
						// redirect if need
						if($arResult['SUCCESS']['ADDED'] && $redirectAfterAdd)
							LocalRedirect($basketUrl, 301);


					}else{
						$arResult['ERROR'][] = GetMessage('NOT_FOUND_PRODUCTS_IN_IBLOCK');
					}
				}else{
					$arResult['ERROR'][] = GetMessage('NOT_FOUND_PRODUCTS_IN_FILE');
				}
			}else{
				$arResult['ERROR'][] = GetMessage('FAILED_TO_PARSE_FILE');
			}
		}else{
			$arResult['ERROR'][] = GetMessage('FILE_NOT_FOUND');
		}
		CFile::Delete($fid);
	}else{
		$arResult['ERROR'][] = GetMessage('FAILED_TO_SAVE_FILE');
	}
}


$this->IncludeComponentTemplate();

?>