<?
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

include('SimpleXLSX.php');
class fileData extends SimpleXLSX{
	public static function getFileData($filePath){
		$filePath = self::findRealFilePath($filePath);

		// return if no file
		if(!$filePath) return array('ERROR' => true,'DATA' => 'File not found');

		// get file data from existsing file
		if($xlsx = SimpleXLSX::parse($filePath))
			return array('SUCCESS' => true,'DATA' => array(
				'ROWS' => $xlsx->rowsEx(),
				'COLUMNS' => self::getColumns($xlsx->rowsEx())
			));
		else
			return array('ERROR' => true,'DATA' => SimpleXLSX::parseError());


		return false;
	}
	private static function getColumns($rows){
		$columnsArr = array();
		foreach(reset($rows) as $key => $cell){
			$columnLetter = preg_replace('/[0-9]/', '', $cell['name']);
			$columnsArr[] = $columnLetter;
		}
		return $columnsArr;
	}
	private static function findRealFilePath($filePath){
		if(file_exists($filePath))
			return $filePath;
		elseif(file_exists($_SERVER['DOCUMENT_ROOT'].$filePath))
			return $_SERVER['DOCUMENT_ROOT'].$filePath;
		else
			return false;
	}
}


if($_REQUEST['getFileData'] == 'Y' && $_REQUEST['filePath']){
	$APPLICATION->RestartBuffer();
	echo json_encode(fileData::getFileData($_REQUEST['filePath']), JSON_UNESCAPED_UNICODE);
	die();
}

?>