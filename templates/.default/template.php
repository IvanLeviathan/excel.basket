<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
	<?if($arResult['ERROR']){?>
		<div class="answers errors">
			<?foreach ($arResult['ERROR'] as $value){?>
				<div class="error"><?=$value?></div>
			<?}?>
		</div>
	<?}?>
	<?if($arResult['SUCCESS']){?>
		<div class="answers successes">
			<?foreach ($arResult['SUCCESS'] as $value){?>
				<div class="success"><?=$value?></div>
			<?}?>
		</div>
	<?}?>

	<?if($arResult['ADD_INFO']['PARTIAL']){?>
		<div class="answers partial_add">
			<?foreach ($arResult['ADD_INFO']['PARTIAL'] as $value){?>
				<div class="partial_add">
					<?=GetMessage('PARTIAL_ADD', array(
						"#UNIQ#"=>$value['UNIQ'],
						"#NAME#" => $value['NAME'],
						"#QUANT#" => $value['QUANT'],
						"#QUANT_NEED#" => $value['QUANT_NEED'],
						"#DETAIL_PAGE_URL#" => $value['DETAIL_PAGE_URL']
					))?>
				</div>
			<?}?>
		</div>
	<?}?>
	<?if($arResult['ADD_INFO']['NOT_AVAILABLE']){?>
		<div class="answers not_available">
			<?foreach ($arResult['ADD_INFO']['NOT_AVAILABLE'] as $value){?>
				<div class="not_available">
					<?=GetMessage('NOT_AVAILABLE',array(
						"#UNIQ#"=>$value['UNIQ'],
						"#NAME#" => $value['NAME'],
						"#QUANT#" => $value['QUANT'],
						"#QUANT_NEED#" => $value['QUANT_NEED'],
						"#DETAIL_PAGE_URL#" => $value['DETAIL_PAGE_URL']
					))?>
				</div>
			<?}?>
		</div>
	<?}?>


	<form method="POST" name="<?=$arResult['FORM_NAME']?>"  enctype="multipart/form-data">
		<?=bitrix_sessid_post()?>
		<input type="hidden" name="SUBMIT_<?=$arResult['FORM_NAME']?>" value="Y">
		<div>
			<label for="file"><?=GetMessage('FILE_INPUT_LABEL')?></label>
			<input type="file" id="file" name="<?=$arResult['FILE_INPUT_NAME']?>" required/> <?/*only 1 file at a time*/?>
		</div>
		<div>
			<button class="btn btn-default"><?=GetMessage('SUBMIT_BTN_TEXT')?></button>
		</div>
	</form>

<??>