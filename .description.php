<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("COMPONENT_NAME"),
	"DESCRIPTION" => GetMessage("PAGE_DESCRIPTION"),
	"ICON" => "/images/icon.gif",
	"SORT" => 20,
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "NEWMARK_EXCELBASKET",
		"NAME" => GetMessage("MAIN_FOLDER"),
		"SORT" => 1930,
	),
	"COMPLEX" => "N",
);
?>