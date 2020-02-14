<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Loader,
	Bitrix\Main\ModuleManager,
	Bitrix\Iblock,
	Bitrix\Catalog,
	Bitrix\Currency;

if (!Loader::includeModule('iblock'))
	return;


$arIBlockType = CIBlockParameters::GetIBlockTypes();

$offersIblock = array();
if ($catalogIncluded)
{
	$iterator = Catalog\CatalogIblockTable::getList(array(
		'select' => array('IBLOCK_ID'),
		'filter' => array('!=PRODUCT_IBLOCK_ID' => 0)
	));
	while ($row = $iterator->fetch())
		$offersIblock[$row['IBLOCK_ID']] = true;
	unset($row, $iterator);
}

$arIBlock = array();
$iblockFilter = (
!empty($arCurrentValues['IBLOCK_TYPE'])
	? array('TYPE' => $arCurrentValues['IBLOCK_TYPE'], 'ACTIVE' => 'Y')
	: array('ACTIVE' => 'Y')
);
$rsIBlock = CIBlock::GetList(array('SORT' => 'ASC'), $iblockFilter);
while ($arr = $rsIBlock->Fetch())
{
	$id = (int)$arr['ID'];
	if (isset($offersIblock[$id]))
		continue;
	$arIBlock[$id] = '['.$id.'] '.$arr['NAME'];
}
unset($id, $arr, $rsIBlock, $iblockFilter);
unset($offersIblock);


$arProperty_LNS = array();

$props = CIBlockParameters::GetFieldCode(GetMessage("IBLOCK_FIELD"), "DATA_SOURCE");

if($props['VALUES'])
	$arProperty_LNS = array_merge($props['VALUES'], $arProperty_LNS);

$rsProp = CIBlockProperty::GetList(array("sort"=>"asc", "name"=>"asc"), array("ACTIVE"=>"Y", "IBLOCK_ID"=>(isset($arCurrentValues["IBLOCK_ID"])?$arCurrentValues["IBLOCK_ID"]:$arCurrentValues["ID"])));
while ($arr=$rsProp->Fetch())
{
	$arProperty[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
	if (in_array($arr["PROPERTY_TYPE"], array("L", "N", "S")))
	{
		$arProperty_LNS['PROPERTY_'.$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
	}
}






$ext = 'xlsx,xls';
$componentPath = '/local/components/nm/excel.basket';


$arComponentParameters = array(
	"GROUPS" => array(
		"FILE_SETTINGS" => array(
			"NAME" => GetMessage("FILE_SETTINGS"),
			"SORT" => '300'
		),
	),
	"PARAMETERS" => array(
		"IBLOCK_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_IBLOCK"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlock,
			"REFRESH" => "Y",
		),
		"UNIQ_CODE" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("T_IBLOCK_PROPERTY"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"VALUES" => $arProperty_LNS,
			"ADDITIONAL_VALUES" => "N",
		),
		"USERTYPE_SYMBOL_CODE" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("USERTYPE_SYMBOL_CODE"),
			"TYPE" => "STRING",
			"DEFAULT" => "BASE",
		),
		"FILE_EXAMPLE" => array(
			"PARENT" => "FILE_SETTINGS",
			"NAME" => GetMessage("FILE_EXAMPLE"),
			"TYPE" => "FILE",
			"FD_TARGET" => "F",
			"FD_EXT" => $ext,
			"FD_UPLOAD" => true,
			"REFRESH" => "Y"
		),
		"CONDITIONS" => array(
			"PARENT" => "FILE_SETTINGS",
			"NAME" => GetMessage("CONDITIONS"),
			"TYPE" => "CUSTOM",
			'JS_FILE' => $componentPath.'/parameters.js',
            'JS_EVENT' => 'OnConditionsInit',
            'JS_DATA' => array(
				"FILE_INPUT_NAME" => "FILE_EXAMPLE",
				"COMPONENT_PATH" => $componentPath,
				"IT_IS_PRODUCT_1" => GetMessage("IT_IS_PRODUCT_1"),
				"IT_IS_PRICE" => GetMessage("IT_IS_PRICE"),
				"IT_IS_QUANT" => GetMessage("IT_IS_QUANT"),
				"IT_IS_UNIQ" => GetMessage("IT_IS_UNIQ"),
				"IT_IS_USERTYPES_SYMBOL" => GetMessage("IT_IS_USERTYPES_SYMBOL"),
				"IT_IS_USERTYPES_PRICE" => GetMessage("IT_IS_USERTYPES_PRICE"),
				"IT_IS_USERTYPES_QUANT" => GetMessage("IT_IS_USERTYPES_QUANT"),
				"AND" => GetMessage("AND"),
				"PLUS" => GetMessage("PLUS"),
				"MINUS" => GetMessage("MINUS"),
				"IS_PRODUCT_ID" => 'condition-is-product',
				"IS_PRICE_ID" => 'condition-is-price',
				"IS_PRICE_SELECT_ID" => 'PRICE_COLUMN',
				"IS_QUANT_ID" => 'condition-is-quant',
				"IS_QUANT_SELECT_ID" => 'QUANT_COLUMN',
				"IS_UNIQ_ID" => 'condition-is-uniq',
				"IS_USERTYPES_ID" => 'condition-is-usertypes',
				"IS_USERTYPES_SUMBOL_ID" => 'USERTYPES_SYMBOL_COLUMN',
				"IS_USERTYPES_PRICE_COLUMN_ID" => 'USERTYPES_PRICE_COLUMN',
				"IS_USERTYPES_QUANT_COLUMN_ID" => 'USERTYPES_QUANT_COLUMN',
				"IS_UNIQ_SELECT_ID" => 'UNIQ_COLUMN',
				"IS_PRODUCT_SELECT_ID" => 'PRODUCT_COLUMN',
				"IS_PRODUCT_CONDITION_ID" =>  'PRODUCT_CONDITION',
				"USERTYPES_MINUS_ID" => 'usertypes-minus',
				"USERTYPES_PLUS_ID" => 'usertypes-plus',
				"IS_PRODUCT_CONDITIONS" => array(
					"NOT_EMPTY" => GetMessage("NOT_EMPTY"),
					"IS_NUMBER" => GetMessage("IS_NUMBER"),
					"IS_EMPTY" => GetMessage('IS_EMPTY'),
					"NOT_NUMBER" => GetMessage('NOT_NUMBER')
				)
			),
			'DEFAULT' => "{\"PRODUCT_COLUMN\":\"A\",\"PRODUCT_CONDITION\":[\"NOT_EMPTY\",\"IS_NUMBER\"],\"UNIQ_COLUMN\":\"A\",\"PRICE_COLUMN\":\"A\",\"QUANT_COLUMN\":\"A\"}",
		),
		"FORM_NAME" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("FORM_NAME"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"REMOVE_OLD_PRODUCTS" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("REMOVE_OLD_PRODUCTS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"BASKET_URL" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("BASKET_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "/basket/",
		),
		"REDIRECT_AFTER_ADD" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("REDIRECT_AFTER_ADD"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"SKIP_INACTIVE_PRODUCTS" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SKIP_INACTIVE_PRODUCTS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
	),
);

?>
