<?
use Bitrix\Seo\Engine;
use Bitrix\Main\Text\Converter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\IO\Path;

\Bitrix\Main\Loader::includeModule('seo');
\Bitrix\Main\Loader::includeModule('socialservices');
global $kit_IBLOCK_ID;       //parser_charset_default

$arType['reference'] = array('html', 'text');
$arType['reference_id'] = array('html', 'text');
$arMode['reference'] = array('debug', 'work');
$arMode['reference_id'] = array('debug', 'work');

//global $DB;
//$DB->Query('ALTER TABLE b_kit_parser ADD TYPE_OUT text;');

$arStore = array();
$arrAdditStores = array();
$arAmount = array();
$arAmount['reference']=array(GetMessage('parser_amount_from_file'));
$arAmount['reference_id']=array('from_file');

if(CModule::IncludeModule("catalog")){
    $arAmount['reference'][]=GetMessage('parser_amount_update_from_stores');
    $arAmount['reference_id'][]='from_stores';
    
    $selectFields = array('ID','TITLE');
    $filter = array("ACTIVE" => "Y");
    $resStore = CCatalogStore::GetList(array(),$filter,false,false,$selectFields);
    while($store = $resStore->Fetch())
    {
        $arStore['reference_id'][] = $store['ID'];
        $arStore['reference'][] = $store['TITLE'];
        if($store["ID"]==$kit_SETTINGS['store']['list'])
            continue;
        $arrAdditStores["reference_id"][] = $store["ID"];
        $arrAdditStores["reference"][] = $store["TITLE"];
    }
}

CModule::IncludeModule("fileman");
CMedialib::Init();
$arCollections = CMedialibCollection::GetList(array('arOrder'=>Array('NAME'=>'ASC'),'arFilter' => array('ACTIVE' => 'Y', 'ML_TYPE'=>'1')));

$arrLibrary=array();
$arrLibrary['reference']=array();
$arrLibrary['reference_id']=array();
foreach($arCollections as $collection){
    $arrLibrary['reference'][]= $collection['NAME'];
    $arrLibrary['reference_id'][]=$collection['ID'];
}

$arOfferLoad['reference'] = array(GetMessage("parser_offer_load_no"), GetMessage("parser_offer_load_table"), GetMessage("parser_offer_load_one"), GetMessage("parser_offer_load_more"));
$arOfferLoad['reference_id'] = array('', 'table', 'one', 'more');

$arUpdate['reference'] = array(GetMessage("parser_update_N"), GetMessage("parser_update_Y"), GetMessage("parser_update_empty"));
$arUpdate['reference_id'] = array('N', 'Y', 'empty');

$arAction['reference'] = array(GetMessage("parser_action_N"), GetMessage("parser_action_A"), GetMessage("parser_action_D"), GetMessage("parser_action_NULL"));
$arAction['reference_id'] = array('N', 'A', 'D', 'NULL');

//$arPriceTerms['reference'] = array(GetMessage("parser_price_terms_no"), GetMessage("parser_price_terms_up"), GetMessage("parser_price_terms_down"));
//$arPriceTerms['reference_id'] = array('', 'up', 'down');

$arPriceTerms['reference'] = array(GetMessage("parser_price_terms_no"), GetMessage("parser_price_terms_delta"));
$arPriceTerms['reference_id'] = array('', 'delta');

$arPriceUpDown['reference'] = array(GetMessage("parser_price_updown_no"), GetMessage("parser_price_updown_up"), GetMessage("parser_price_updown_down"));
$arPriceUpDown['reference_id'] = array('', 'up', 'down');

$arPriceValue['reference'] = array(GetMessage("parser_price_percent"), GetMessage("parser_price_abs_value"));
$arPriceValue['reference_id'] = array('percent', 'value');

$arAuthType['reference'] = array(GetMessage("parser_auth_type_form"), GetMessage("parser_auth_type_http"));
$arAuthType['reference_id'] = array('form', 'http');

$arDopUrl["reference"][] = GetMessage("parser_section_all");
$arDopUrl["reference_id"][] = "section_all";
if(isset($kit_SECTION_ID) && !empty($kit_SECTION_ID))
{
    $arDopUrl["reference_id"][] = $kit_SECTION_ID;
    foreach($arSection["REFERENCE_ID"] as $id_sec => $text)
    {
        if($text == $kit_SECTION_ID)
        {
            $arDopUrl["reference"][] = str_replace(array("."), "", $arSection["REFERENCE"][$id_sec]);
            break 1;
        }
    }
}

if(isset($kit_SETTINGS["catalog"]["section_dop"]) && !empty($kit_SETTINGS["catalog"]["section_dop"]))
{
    foreach($kit_SETTINGS["catalog"]["section_dop"] as $sectionId)
    {
        if(in_array($sectionId, $arDopUrl["reference_id"])) continue 1;
        $arDopUrl["reference_id"][] = $sectionId;
        if(is_array($arSection))
        foreach($arSection["REFERENCE_ID"] as $id_sec => $text)
        {
            if($text == $sectionId)
            {
                $arDopUrl["reference"][] = str_replace(array("."), "", $arSection["REFERENCE"][$id_sec]);
                break 1;
            }
        }
    }
}


$hideCatalog = false;
$arPricesNames = array();
if($isCatalog && CModule::IncludeModule('catalog') && CModule::IncludeModule('currency')/* && (($kit_IBLOCK_ID && CCatalog::GetList(Array("name" => "asc"), Array("ACTIVE"=>"Y", "ID"=>$kit_IBLOCK_ID))->Fetch()) || !$kit_IBLOCK_ID)*/)
{
    $selectedPriceName = CCatalogGroup::GetByID($kit_SETTINGS['catalog']['price_type']);
    $selectedPriceName = $selectedPriceName["NAME_LANG"]?:$selectedPriceName["NAME"];
    
    $dbPriceType = CCatalogGroup::GetList(
        array("SORT" => "ASC"),
        array()
    );
    
    while ($arPriceTypes = $dbPriceType->Fetch())
    {
        $arPriceType["reference"][] = $arPriceTypes["NAME_LANG"]?:$arPriceTypes["NAME"];
        $arPriceType["reference_id"][] = $arPriceTypes["ID"];
        $arPricesNames[$arPriceTypes["ID"]]=$arPriceTypes["NAME_LANG"]?:$arPriceTypes["NAME"];
        if($arPriceTypes["ID"]==$kit_SETTINGS['catalog']['price_type'])
            continue;
        $arAdditPriceType["reference"][] = $arPriceTypes["NAME_LANG"]?:$arPriceTypes["NAME"];
        $arAdditPriceType["reference_id"][] = $arPriceTypes["ID"];
    }
    
    $arConvertCurrency["reference"][] = GetMessage("parser_convert_no");
    $arConvertCurrency["reference_id"][] = "";
    
    $arPriceOkrug["reference"] = array(GetMessage("parser_price_okrug_no"), GetMessage("parser_price_okrug_up"), GetMessage("parser_price_okrug_ceil"), GetMessage("parser_price_okrug_floor"));
    $arPriceOkrug["reference_id"] = array("", "up", "ceil", "floor");
    
    $lcur = CCurrency::GetList(($by="name"), ($order1="asc"), LANGUAGE_ID);
    while($lcur_res = $lcur->Fetch())
    {
        $arCurrency["reference"][] = $lcur_res["FULL_NAME"];
        $arCurrency["reference_id"][] = $lcur_res["CURRENCY"];
        $arConvertCurrency["reference"][] = $lcur_res["FULL_NAME"];
        $arConvertCurrency["reference_id"][] = $lcur_res["CURRENCY"];
    }
    
    $info = CModule::CreateModuleObject('catalog');
    
    if(!CheckVersion("14.0.0", $info->MODULE_VERSION))
    {
        $dbResultList = CCatalogMeasure::getList(array(), array(), false, false, array("ID", "CODE", "MEASURE_TITLE", "SYMBOL_INTL", "IS_DEFAULT"));
        while($arMeasure = $dbResultList->Fetch())
        {
            $arAllMeasure["reference_id"][] = $arMeasure["ID"];
            $arAllMeasure["reference"][] = $arMeasure["MEASURE_TITLE"];
        }
    }
    
    $arVATRef = CatalogGetVATArray(array(), true);

}else $hideCatalog = true;

/*
if(isset($arrPropDop))
{
    $arrPropField['REFERENCE'] = array_merge($arrPropField['REFERENCE'], $arrPropDop["REFERENCE"]);
    $arrPropField['REFERENCE_ID'] = array_merge($arrPropField['REFERENCE_ID'], $arrPropDop["REFERENCE_ID"]);
}*/

$arrActionProps['REFERENCE'] = array(GetMessage("parser_action_props_delete"), GetMessage("parser_action_props_add_begin"), GetMessage("parser_action_props_add_end"), GetMessage("parser_action_props_to_lower"));
$arrActionProps['REFERENCE_ID'] = array("delete", "add_b", "add_e", "lower");

$disabled = false;
$disabledType = false;
unset($arrDateActive['REFERENCE'][2]);
unset($arrDateActive['REFERENCE_ID'][2]);
$arrDate = ParseDateTime($kit_START_LAST_TIME_X, "YYYY.MM.DD HH:MI:SS");
if($kit_TYPE)$disabled  = 'disabled=""';
if($kit_TYPE_OUT)$disabledType  = 'disabled=""';
$tabControl->BeginNextTab();
?>
    <tr>
        <td><?echo GetMessage("parser_type")?></td>
        <td><?=SelectBoxFromArray('TYPE', $arTypeParser, $kit_TYPE?$kit_TYPE:$_GET["type"], "", $disabled);?>
        <?if($disabled):?><input type="hidden" name="TYPE" value="<?=$kit_TYPE?>" /><?endif;?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_type_out")?></td>
        <td><?=SelectBoxFromArray('TYPE_OUT', $arTypeOut, $kit_TYPE_OUT?$kit_TYPE_OUT:$_GET["type_out"], "", $disabledType);?>
        <?if($disabledType):?><input type="hidden" name="TYPE_OUT" value="<?=$kit_TYPE_OUT?>" /><?endif;?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_mode")?></td>
        <td><?=SelectBoxFromArray('SETTINGS[catalog][mode]', $arMode, $kit_SETTINGS["catalog"]["mode"]?$kit_SETTINGS["catalog"]["mode"]:"debug", "", "");?></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_mode_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_act")?></td>
        <td width="60%"><input type="checkbox" name="ACTIVE" value="Y"<?if($kit_ACTIVE == "Y" || !$ID) echo " checked"?>>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_sort")?></td>
        <td><input type="text" name="SORT" value="<?echo !$ID?"100":$kit_SORT;?>" size="4"></td>
    </tr>
    <?if(isset($arCategory) && !empty($arCategory)):?>
    <tr>
        <td><?echo GetMessage("parser_category_title")?></td>
        <td><?=SelectBoxFromArray('CATEGORY_ID', $arCategory, isset($kit_CATEGORY_ID)?$kit_CATEGORY_ID:$parentID, GetMessage("parser_category_select"), "id='category' style='width:262px'");?></td>
    </tr>
    <?endif;?>
    <tr>
        <td><span class="required">*</span><?echo GetMessage("parser_name")?></td>
        <td><input type="text" name="NAME" value="<?echo $kit_NAME;?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td><span class="required">*</span><?echo GetMessage("parser_iblock_id_catalog")?></td>
        <td><?=SelectBoxFromArray('IBLOCK_ID', $arIBlock, $kit_IBLOCK_ID, GetMessage("parser_iblock_id"), "id='iblock' style='width:262px' ");?>
            <?/*?><?if($disabled):?><input type="hidden" name="IBLOCK_ID" value="<?=$kit_IBLOCK_ID?>" /><?endif;*/?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_section_id")?></td>
        <td><?=SelectBoxFromArray('SECTION_ID', $arSection, $kit_SECTION_ID, GetMessage("parser_section_id"), "id='section' style='width:262px'");?></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_iblock_id_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><span class="required">*</span><?echo GetMessage("parser_rss_catalog")?></td>
        <td><input type="text" name="RSS" value="<?echo $kit_RSS;?>" size="80" maxlength="500"></td>
    </tr>
    <tr>
        <td style="vertical-align:top"><?echo GetMessage("parser_url_dop")?></td>
        <td>
            <textarea name="SETTINGS[catalog][url_dop]" cols="65" rows="5"><?=$kit_SETTINGS["catalog"]["url_dop"]?></textarea>
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_url_dop_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?
    if(isset($kit_SETTINGS["catalog"]["rss_dop"]) && !empty($kit_SETTINGS["catalog"]["rss_dop"])):?>
        <?$count_url = 0;?>
        <?foreach($kit_SETTINGS["catalog"]["rss_dop"] as $key => $dop_rss):?>
            <?
//                $dop_rss = trim($dop_rss);
                if(!$dop_rss) continue 1;
                $count_url++;
            ?>
            <tr class="admin_tr_rss_dop" data-id="<?php echo $key;?>">
                <td class="adm-detail-content-cell-l"><?=GetMessage("parser_dop_load_rss").$count_url;?></td>
                <td class="adm-detail-content-cell-r"><input type="text" maxlength="500" size="50" value="<?=$dop_rss;?>" name="SETTINGS[catalog][rss_dop][<?=$key;?>]">
                    <?=SelectBoxFromArray("SETTINGS[catalog][section_dop][".$key."]", $arSection, isset($kit_SETTINGS["catalog"]["section_dop"][$key])?$kit_SETTINGS["catalog"]["section_dop"][$key]:"", GetMessage("parser_section_id"), "style='width:262px;'");?>
                    <a class="dop_rss_delete" href="#"><?=GetMessage("parser_caption_detete_button");?></a>
                </td>
            </tr>
        <?endforeach;?>
    <?endif?>
    <tr>
        <td colspan="2" align="center">
            <span id="loadDopRSS" class="adm-btn"><?=GetMessage("parser_add_rss_dop_button")?></span>
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_rss_dop_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_encoding")?></td>
        <td><?=SelectBoxFromArray('ENCODING', $arEncoding, $kit_ENCODING);?></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_encoding_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_step")?></td>
        <td><input type="text" name="SETTINGS[catalog][step]" value="<?echo $kit_SETTINGS["catalog"]["step"]?$kit_SETTINGS["catalog"]["step"]:30;?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_start_last_time")?></td>
        <td><input type="text" disabled name="START_LAST_TIME_X" value="<?echo $arrDate[DD].'.'.$arrDate[MM].'.'.$arrDate[YYYY].' '.$arrDate[HH].':'.$arrDate[MI].':'.$arrDate[SS];?>" size="20"></td>
    </tr>
<?
//********************
//Auto params
//********************
$tabControl->BeginNextTab();
?>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_standart_pagenavigation")?></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_pagenavigation_selector")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][pagenavigation_selector]" value="<?echo $kit_SETTINGS["catalog"]["pagenavigation_selector"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_pagenavigation_selector_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_pagenavigation_one")?></td>
        <td><input type="text" name="SETTINGS[catalog][pagenavigation_one]" value="<?echo $kit_SETTINGS["catalog"]["pagenavigation_one"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_pagenavigation_one_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_pagenavigation_delete")?></td>
        <td><input type="text" name="SETTINGS[catalog][pagenavigation_delete]" value="<?echo $kit_SETTINGS["catalog"]["pagenavigation_delete"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_pagenavigation_begin")?></td>
        <td><input type="text" name="SETTINGS[catalog][pagenavigation_begin]" value="<?echo $kit_SETTINGS["catalog"]["pagenavigation_begin"];?>" size="5" maxlength="5"> <?echo GetMessage("parser_pagenavigation_end")?> <input type="text" name="SETTINGS[catalog][pagenavigation_end]" value="<?echo $kit_SETTINGS["catalog"]["pagenavigation_end"];?>" size="5" maxlength="5"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_pagenavigation_begin_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_work_pagenavigation")?></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_work_pagenavigation_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_work_pagenavigation_var")?>:</td>
        <td><input type="text" name="SETTINGS[catalog][pagenavigation_var]" value="<?echo $kit_SETTINGS["catalog"]["pagenavigation_var"];?>" size="10" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_work_pagenavigation_var_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_work_pagenavigation_var_step")?>:</td>
        <td><input type="text" name="SETTINGS[catalog][pagenavigation_var_step]" value="<?echo $kit_SETTINGS["catalog"]["pagenavigation_var_step"];?>" size="10" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_work_pagenavigation_var_step_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_work_pagenavigation_other_var")?>:</td>
        <td><input type="text" name="SETTINGS[catalog][pagenavigation_other_var]" value="<?echo $kit_SETTINGS["catalog"]["pagenavigation_other_var"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_work_pagenavigation_other_var_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_work_pagenavigation_page_count")?>:</td>
        <td><input type="text" name="SETTINGS[catalog][pagenavigation_page_count]" value="<?echo $kit_SETTINGS["catalog"]["pagenavigation_page_count"];?>" size="10" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_work_pagenavigation_page_count_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    
<?
//********************
//Attachments
//********************
$tabControl->BeginNextTab();
?>  <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_selector_preview_catalog")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][selector]" value="<?echo $kit_SETTINGS["catalog"]["selector"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_selector_catalog_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_href_catalog")?></td>
        <td><input type="text" name="SETTINGS[catalog][href]" value="<?echo $kit_SETTINGS["catalog"]["href"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_href_descr_catalog")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_name_catalog")?></td>
        <td><input type="text" name="SETTINGS[catalog][name]" value="<?echo $kit_SETTINGS["catalog"]["name"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_name_descr_page")?>
            <?=EndNote();?>
        </td>
    </tr>
    
    <tr>
        <td><?echo GetMessage("parser_preview_price").$selectedPriceName.':';?></td>
        <td><input type="text" name="SETTINGS[catalog][preview_price]" value="<?echo $kit_SETTINGS["catalog"]["preview_price"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_preview_price_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_more_prices")?></td>
    </tr>
    <?php
    if(isset($kit_SETTINGS['prices_preview']) && !empty($kit_SETTINGS['prices_preview'])){
        foreach($kit_SETTINGS['prices_preview'] as $id_price => $price){
        ?>
        <tr class="adittional_preview_prices_id_<?php echo $id_price;?>">
            <td><?php echo GetMessage('parser_preview_price').$price['name'].'['.$id_price.']:';?></td>
            <td>
                <input type="text" name="SETTINGS[prices_preview][<?php echo $id_price;?>][value]" value="<?echo $price["value"];?>" size="40" maxlength="250">&nbsp;<a href="#" class="price_delete" data-price-id="<?php echo $id_price;?>">Delete</a>
                <input type="hidden" name="SETTINGS[prices_preview][<?php echo $id_price;?>][name]" value="<?echo $price["name"];?>">
            </td>
        </tr>
        <?php
        }
    }
    ?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPricetypes', $arAdditPriceType, "", GetMessage("kit_parser_addit_prices"), "");?>
            <input type="submit" id="addPrice" name="refresh" value="<?=GetMessage("kit_parser_add_addit_prices")?>">
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_addit_price_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"> </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_preview_count")?></td>
        <td><input type="text" name="SETTINGS[catalog][preview_count]" value="<?echo $kit_SETTINGS["catalog"]["preview_count"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_preview_count_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_preview_text_selector")?></td>
        <td><input type="text" name="SETTINGS[catalog][preview_text_selector]" value="<?echo $kit_SETTINGS["catalog"]["preview_text_selector"];?>" size="40" maxlength="250"></td>
    </tr>

    <tr>
        <td><?echo GetMessage("parser_preview_text_type_catalog")?></td>
        <td><?=SelectBoxFromArray('PREVIEW_TEXT_TYPE', $arType, $kit_PREVIEW_TEXT_TYPE, "", "");?></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_preview_delete_tag_catalog")?></td>
        <td><input class="bool-delete" type="checkbox" name="BOOL_PREVIEW_DELETE_TAG" value="Y"<?if($kit_BOOL_PREVIEW_DELETE_TAG == "Y") echo " checked"?>> <?echo GetMessage("parser_bool_preview_delete_tag")?><input <?if($kit_BOOL_PREVIEW_DELETE_TAG != "Y"):?>disabled <?endif?> type="text" name="PREVIEW_DELETE_TAG" value="<?echo $kit_PREVIEW_DELETE_TAG;?>" size="40" maxlength="300"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_preview_delete_tag_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_preview_delete_element")?></td>
        <td width="60%"><input size="40" maxlength="300" type="text" name="PREVIEW_DELETE_ELEMENT" value="<?=$kit_PREVIEW_DELETE_ELEMENT?>"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_preview_delete_element_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_preview_delete_attribute")?></td>
        <td width="60%"><input size="40" maxlength="300" type="text" name="PREVIEW_DELETE_ATTRIBUTE" value="<?=$kit_PREVIEW_DELETE_ATTRIBUTE?>"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_preview_delete_attribute_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_preview_first_img_catalog")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][preview_picture]" value="<?echo isset($kit_SETTINGS["catalog"]["preview_picture"])?$kit_SETTINGS["catalog"]["preview_picture"]:"img:eq(0)[src]";?>" size="40" maxlength="255" /></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_preview_first_img_descr_catalog")?>
            <?=EndNote();?>
        </td>
    </tr>
    
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_more_stores")?></td>
    </tr>
    <?php
    if(isset($kit_SETTINGS['addit_stores_preview']) && !empty($kit_SETTINGS['addit_stores_preview'])){
        foreach($kit_SETTINGS['addit_stores_preview'] as $id_store => $store){
        ?>
        <tr class="adittional_store_preview_id_<?php echo $id_store;?>">
            <td><?php echo GetMessage('parser_addit_store').$store['name'].'['.$id_store.']:';?></td>
            <td>
                <input type="text" name="SETTINGS[addit_stores_preview][<?php echo $id_store;?>][value]" value="<?echo $store["value"];?>" size="40" maxlength="250">&nbsp;<a href="#" class="store_delete" data-store-id="<?php echo $id_store;?>">Delete</a>
                <input type="hidden" name="SETTINGS[addit_stores_preview][<?php echo $id_store;?>][name]" value="<?echo $store["name"];?>">
            </td>
        </tr>
        <?php
        }
    }
    ?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrAdditStores_preview', $arrAdditStores, "", GetMessage("kit_parser_addit_stores"), "");?>
            <input type="submit" id="addStore_preview" name="refresh" value="<?=GetMessage("kit_parser_add_addit_stores")?>">
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_addit_store_descr_catalog")?>
            <?=EndNote();?>
        </td>
    </tr>


<?
//********************
//Detail page
//********************
$tabControl->BeginNextTab();
?>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_selector_detail_catalog")?></td>
        <td width="60%"><input type="text" name="SELECTOR" value="<?echo $kit_SELECTOR;?>" size="40" maxlength="250"></td>
    </tr>

    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_selector_detail_catalog_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_detail_name")?></td>
        <td><input type="text" name="SETTINGS[catalog][detail_name]" value="<?echo $kit_SETTINGS["catalog"]["detail_name"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_detail_name_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_preview_price").$selectedPriceName?></td>
        <td><input type="text" name="SETTINGS[catalog][detail_price]" value="<?echo $kit_SETTINGS["catalog"]["detail_price"];?>" size="40" maxlength="250"></td>
    </tr>
    
    
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_more_prices")?></td>
    </tr>
    <?php
    if(isset($kit_SETTINGS['prices_detail']) && !empty($kit_SETTINGS['prices_detail'])){
        foreach($kit_SETTINGS['prices_detail'] as $id_price_d => $price_d){
        ?>
        <tr class="adittional_detail_prices_id_<?php echo $id_price_d;?>">
            <td><?php echo GetMessage('parser_preview_price').$price_d['name'].'['.$id_price_d.']:';?></td>
            <td>
                <input type="text" name="SETTINGS[prices_detail][<?php echo $id_price_d;?>][value]" value="<?echo $price_d["value"];?>" size="40" maxlength="250">&nbsp;<a href="#" class="price_delete" data-price-id="<?php echo $id_price_d;?>">Delete</a>
                <input type="hidden" name="SETTINGS[prices_detail][<?php echo $id_price_d;?>][name]" value="<?echo $price_d["name"];?>">
            </td>
        </tr>
        <?php
        }
    }
    ?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPricetypes', $arAdditPriceType, "", GetMessage("kit_parser_addit_prices"), "");?>
            <input type="submit" id="addPriceDetail" name="refresh" value="<?=GetMessage("kit_parser_add_addit_prices")?>">
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_addit_price_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"> </td>
    </tr>
    
    <tr>
        <td><?echo GetMessage("parser_detail_count")?></td>
        <td><input type="text" name="SETTINGS[catalog][detail_count]" value="<?echo $kit_SETTINGS["catalog"]["detail_count"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_detail_count_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_detail_text_selector")?></td>
        <td><input type="text" name="SETTINGS[catalog][detail_text_selector]" value="<?echo $kit_SETTINGS["catalog"]["detail_text_selector"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_detail_text_type_catalog")?></td>
        <td><?=SelectBoxFromArray('DETAIL_TEXT_TYPE', $arType, $kit_DETAIL_TEXT_TYPE, "", "");?></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_detail_delete_tag")?></td>
        <td><input class="bool-delete" type="checkbox" name="BOOL_DETAIL_DELETE_TAG" value="Y"<?if($kit_BOOL_DETAIL_DELETE_TAG == "Y") echo " checked"?>> <?echo GetMessage("parser_bool_detail_delete_tag")?><input <?if($kit_BOOL_DETAIL_DELETE_TAG != "Y"):?>disabled <?endif?> type="text" name="DETAIL_DELETE_TAG" value="<?echo $kit_DETAIL_DELETE_TAG;?>" size="40" maxlength="300"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_preview_delete_tag_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_detail_delete_element")?></td>
        <td width="60%"><input size="40" maxlength="300" type="text" name="DETAIL_DELETE_ELEMENT" value="<?=$kit_DETAIL_DELETE_ELEMENT?>"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_detail_delete_element_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_detail_delete_attribute")?></td>
        <td width="60%"><input size="40" maxlength="300" type="text" name="DETAIL_DELETE_ATTRIBUTE" value="<?=$kit_DETAIL_DELETE_ATTRIBUTE?>"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_detail_delete_attribute_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_detail_first_img_catalog")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][detail_picture]" value="<?echo isset($kit_SETTINGS["catalog"]["detail_picture"])?$kit_SETTINGS["catalog"]["detail_picture"]:"img:eq(0)[src]";?>" size="40" maxlength="255" /></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_detail_first_img_descr_catalog")?>
            <?=EndNote();?>
        </td>
    </tr>
    
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_more_stores")?></td>
    </tr>
    <?php
    if(isset($kit_SETTINGS['addit_stores']) && !empty($kit_SETTINGS['addit_stores'])){
        foreach($kit_SETTINGS['addit_stores'] as $id_store => $store){
        ?>
        <tr class="adittional_store_id_<?php echo $id_store;?>">
            <td><?php echo GetMessage('parser_addit_store').$store['name'].'['.$id_store.']:';?></td>
            <td>
                <input type="text" name="SETTINGS[addit_stores][<?php echo $id_store;?>][value]" value="<?echo $store["value"];?>" size="40" maxlength="250">&nbsp;<a href="#" class="store_delete" data-store-id="<?php echo $id_store;?>">Delete</a>
                <input type="hidden" name="SETTINGS[addit_stores][<?php echo $id_store;?>][name]" value="<?echo $store["name"];?>">
            </td>
        </tr>
        <?php
        }
    }
    ?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrAdditStores', $arrAdditStores, "", GetMessage("kit_parser_addit_stores"), "");?>
            <input type="submit" id="addStore" name="refresh" value="<?=GetMessage("kit_parser_add_addit_stores")?>">
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_addit_store_descr_catalog")?>
            <?=EndNote();?>
        </td>
    </tr>

<?
//Properties
$tabControl->BeginNextTab();
?>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_more_image")?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_more_image_prop")?></td>
        <td width="60%"><?=SelectBoxFromArray('SETTINGS[catalog][more_image_props]', $arrPropFile, $kit_SETTINGS["catalog"]["more_image_props"], GetMessage("parser_prop_id"), "class='image_props'");?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_selector_more_image")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][more_image]" value="<?echo $kit_SETTINGS["catalog"]["more_image"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_selector_more_image_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading" id="header_selector_prop">
        <td colspan="2"><?echo GetMessage("parser_default_props")?></td>
    </tr>
    <?if(isset($kit_SETTINGS["catalog"]["default_prop"]) && !empty($kit_SETTINGS["catalog"]["default_prop"])):?>
    <?foreach($kit_SETTINGS["catalog"]["default_prop"] as $code=>$val):
//        $val = trim($val);
        if(!$val) continue 1;
    ?>
    <tr>
        <td width="40%"><?=$arrPropDop['REFERENCE_CODE_NAME'][$code]?>&nbsp;[<?=$code?>]:</td>
        <td width="60%">
            <?if($arrPropDop['REFERENCE_TYPE'][$code]=="L"):
            ?>
            <?=SelectBoxFromArray('SETTINGS[catalog][default_prop]['.$code.']', $arrPropDop["LIST_VALUES"][$code], $kit_SETTINGS["catalog"]["default_prop"][$code], "", "");?>
            <?elseif($arrPropDop['USER_TYPE'][$code]=="directory"):?>
            <?=SelectBoxFromArray('SETTINGS[catalog][default_prop]['.$code.']', $arrPropDop["LIST_VALUES"][$code], $kit_SETTINGS["catalog"]["default_prop"][$code], "", "");?>
            <?else:?>
            <input type="text" <?if(!$kit_SETTINGS["catalog"]["default_prop"][$code]):?>placeholder="<?=GetMessage("parser_prop_default")?>"<?endif;?> name="SETTINGS[catalog][default_prop][<?=$code?>]" value="<?=$kit_SETTINGS["catalog"]["default_prop"][$code]?>" />
            <?endif?>
        </td>
    </tr>
    <?endforeach;?>
    <?endif;
    $arrPropDopDefault = $arrPropDop;
    unset($arrPropDopDefault['REFERENCE'][0]);
    unset($arrPropDopDefault['REFERENCE_ID'][0]);
    
    ?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPropDefault', $arrPropDopDefault, "", GetMessage("kit_parser_select_prop"), "");?>
            <input type="submit" id="loadPropDefault" name="refresh" value="<?=GetMessage("kit_parser_select_prop_but")?>">
        </td>
    </tr>
    
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_filter_props")?></td>
    </tr>
    
    <tr>
        <td width="40%"><?echo GetMessage("parser_props_filter")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][enable_props_filter]" value="Y"<?if($kit_SETTINGS["catalog"]["enable_props_filter"] == "Y") echo " checked"?>>
        </td>
    </tr>
    <?
    if(isset($kit_SETTINGS["props_filter_value"]) && count($kit_SETTINGS["props_filter_value"]) > 0)
    {
        foreach($kit_SETTINGS["props_filter_value"] as $id => $propsfilter)
        {
            foreach($propsfilter as $code => $val)
            {
                if(empty($code) || empty($val)) continue 1;
?>
                <tr>
                    <td width="40%"><?=$arrPropDop['REFERENCE_CODE_NAME'][$code]?>&nbsp;[<?=$code?>]</td>
                    <td width="60%">
                        <?=SelectBoxFromArray("SETTINGS[props_filter_circs][$id][$code]", $arrFilterCircs, $kit_SETTINGS["props_filter_circs"][$id][$code]?$kit_SETTINGS["props_filter_circs"][$id][$code]:"equally", "", "");?>
                        <input type="text" size="40" data-code="<?=$code?>" name="SETTINGS[props_filter_value][<?=$id?>][<?=$code?>]" value="<?=$val?>">
                        <a href="#" class="prop_delete">Delete</a>
                    </td>
                </tr>
<?
            }
         }
    }
    ?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPropsFilter', $arrPropDop, "", GetMessage("kit_parser_select_prop"), "");?>
            <input type="submit" id="loadFilterProps" name="refresh" value="<?=GetMessage("kit_parser_select_prop_but")?>">
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_filter_props_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading" id="header_selector_prop">
        <td colspan="2"><?echo GetMessage("parser_selector_props")?></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_catalog_delete_symb")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][catalog_delete_selector_props_symb]" value="<?echo $kit_SETTINGS["catalog"]["catalog_delete_selector_props_symb"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_delete_symb_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?if(isset($kit_SETTINGS["catalog"]["selector_prop"]) && !empty($kit_SETTINGS["catalog"]["selector_prop"])):?>
    <?foreach($kit_SETTINGS["catalog"]["selector_prop"] as $code=>$val):
//        $val = trim($val);
        if(!$val) continue 1;
    ?>
    <tr>
        <td width="40%"><?=$arrPropDop['REFERENCE_CODE_NAME'][$code]?>&nbsp;[<?=$code?>]:</td>
        <td width="60%">
            <input type="text" size="40" data-code="<?=$code?>" name="SETTINGS[catalog][selector_prop][<?=$code?>]" value="<?=$kit_SETTINGS["catalog"]["selector_prop"][$code]?>">
        </td>
    </tr>
    <?endforeach?>
    <?endif;?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPropDop', $arrPropDop, "", GetMessage("kit_parser_select_prop"), "");?>
            <input type="submit" id="loadDopProp" name="refresh" value="<?=GetMessage("kit_parser_select_prop_but")?>">
        </td>
    </tr>
    <tr style="display:none">
        <td colspan="2"><input type="hidden" id="delete_selector_prop" name="SETTINGS[catalog][delete_selector_prop]" value="<?=$kit_SETTINGS["catalog"]["delete_selector_prop"]?>" /></td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_prop_detail_preview_descr")?><?echo GetMessage("parser_prop_detail_preview_descr_file")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading" id="header_find_prop">
        <td colspan="2"><?echo GetMessage("parser_find_props")?></td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"><?echo GetMessage("parser_selector_find_props")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][selector_find_props]" value="<?echo $kit_SETTINGS["catalog"]["selector_find_props"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_selector_find_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"><?echo GetMessage("parser_catalog_delete_symb")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][catalog_delete_selector_find_props_symb]" value="<?echo $kit_SETTINGS["catalog"]["catalog_delete_selector_find_props_symb"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_delete_symb_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?if(isset($kit_SETTINGS["catalog"]["find_prop"]) && !empty($kit_SETTINGS["catalog"]["find_prop"])):?>
    <?foreach($kit_SETTINGS["catalog"]["find_prop"] as $code=>$val):
//    $val = trim($val);
    if(!$val) continue 1;
    ?>
    <tr>
        <td width="40%"><?=$arrPropDop['REFERENCE_CODE_NAME'][$code]?>&nbsp;[<?=$code?>]:</td>
        <td width="60%"><input type="text" size="40" data-code="<?=$code?>" name="SETTINGS[catalog][find_prop][<?=$code?>]" value="<?=$kit_SETTINGS["catalog"]["find_prop"][$code]?>">&nbsp;<a class="find_delete" href="#">Delete</a></td>
    </tr>
    <?endforeach;?>
    <?endif;?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPropDop1', $arrPropDop, "", GetMessage("kit_parser_select_prop"), "");?>
            <input type="submit" id="loadDopProp1" name="refresh" value="<?=GetMessage("kit_parser_select_prop_but")?>">
        </td>
    </tr>
    
    <tr style="display:none">
        <td colspan="2"><input type="hidden" id="delete_find_prop" name="SETTINGS[catalog][delete_find_prop]" value="<?=$kit_SETTINGS["catalog"]["delete_find_prop"]?>" /></td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_prop_detail_preview_descr")?><?echo GetMessage("parser_prop_detail_preview_descr_file")?>
            <?=EndNote();?>
        </td>
    </tr>
    
    <tr class="heading" id="header_selector_prop">
        <td colspan="2"><?echo GetMessage("parser_selector_props_preview")?></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_catalog_delete_symb")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][catalog_delete_selector_props_symb_preview]" value="<?echo $kit_SETTINGS["catalog"]["catalog_delete_selector_props_symb_preview"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_delete_symb_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?if(isset($kit_SETTINGS["catalog"]["selector_prop_preview"]) && !empty($kit_SETTINGS["catalog"]["selector_prop_preview"])):?>
    <?foreach($kit_SETTINGS["catalog"]["selector_prop_preview"] as $code=>$val):
//        $val = trim($val);
        if(!$val) continue 1;
    ?>
    <tr>
        <td width="40%"><?=$arrPropDop['REFERENCE_CODE_NAME'][$code]?>&nbsp;[<?=$code?>]:</td>
        <td width="60%">
            <input type="text" size="40" data-code="<?=$code?>" name="SETTINGS[catalog][selector_prop_preview][<?=$code?>]" value="<?=$kit_SETTINGS["catalog"]["selector_prop_preview"][$code]?>">
            <a class="prop_delete" href="#">Delete</a>
        </td>
    </tr>
    <?endforeach?>
    <?endif;?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPropDop', $arrPropDop, "", GetMessage("kit_parser_select_prop"), "");?>
            <input type="submit" id="loadDopProp2" name="refresh" value="<?=GetMessage("kit_parser_select_prop_but")?>">
        </td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_prop_detail_preview_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading" id="header_find_prop">
        <td colspan="2"><?echo GetMessage("parser_find_props_preview")?></td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"><?echo GetMessage("parser_selector_find_props")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][selector_find_props_preview]" value="<?echo $kit_SETTINGS["catalog"]["selector_find_props_preview"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_selector_find_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"><?echo GetMessage("parser_catalog_delete_symb")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][catalog_delete_selector_find_props_symb_preview]" value="<?echo $kit_SETTINGS["catalog"]["catalog_delete_selector_find_props_symb_preview"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_delete_symb_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?if(isset($kit_SETTINGS["catalog"]["find_prop_preview"]) && !empty($kit_SETTINGS["catalog"]["find_prop_preview"])):?>
    <?foreach($kit_SETTINGS["catalog"]["find_prop_preview"] as $code=>$val):
//    $val = trim($val);
    if(!$val) continue 1;
    ?>
    <tr>
        <td width="40%"><?=$arrPropDop['REFERENCE_CODE_NAME'][$code]?>&nbsp;[<?=$code?>]:</td>
        <td width="60%"><input type="text" size="40" data-code="<?=$code?>" name="SETTINGS[catalog][find_prop_preview][<?=$code?>]" value="<?=$kit_SETTINGS["catalog"]["find_prop_preview"][$code]?>">&nbsp;<a class="find_delete" href="#">Delete</a></td>
    </tr>
    <?endforeach;?>
    <?endif;?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPropDop1', $arrPropDop, "", GetMessage("kit_parser_select_prop"), "");?>
            <input type="submit" id="loadDopProp3" name="refresh" value="<?=GetMessage("kit_parser_select_prop_but")?>">
        </td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_prop_detail_preview_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading" id="header_find_prop">
        <td colspan="2"><?echo GetMessage("parser_add_delete_symb_props")?></td>
    </tr>
    <?if(isset($kit_SETTINGS["catalog"]["action_props_val"]) && !empty($kit_SETTINGS["catalog"]["action_props_val"])):?>
    <?foreach($kit_SETTINGS["catalog"]["action_props_val"] as $code=>$arVal):
        foreach($arVal as $i=>$val):
//        $val = trim($val);
        if(!$val) continue 1;
    ?>
    <tr>
        <td width="40%"><?=($code=="COLLECTED_PARSER_NAME_E")?GetMessage("parser_COLLECTED_PARSER_NAME_E"):$arrPropDop['REFERENCE_CODE_NAME'][$code]?>&nbsp;<?if($code=="COLLECTED_PARSER_NAME_E"):?><?else:?>[<?=$code?>]<?endif;?>:</td>
        <td width="60%"><input type="text" size="40" data-code="<?=$code?>" name="SETTINGS[catalog][action_props_val][<?=$code?>][]" value="<?=$val?>">&nbsp; <?=SelectBoxFromArray('SETTINGS[catalog][action_props]['.$code.']['.$i.']', $arrActionProps, $kit_SETTINGS["catalog"]["action_props"][$code][$i], GetMessage("kit_parser_select_action_props"), "");?> <a class="find_delete" href="#">Delete</a></td>
    </tr>
        <?endforeach;?>
    <?endforeach;?>
    <?endif;?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPropField', $arrPropField, "", GetMessage("kit_parser_select_prop"), "");?>
            <input type="submit" id="loadPropField" name="refresh" value="<?=GetMessage("kit_parser_select_prop_but")?>">
        </td>
    </tr>
    <tr class="tr_find_prop">
    <?
    ?>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("kit_parser_action_props_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
<?
if(!$hideCatalog):
//Catalog
$tabControl->BeginNextTab();
?>
    <?if($isOfferCatalog):?>
    <tr>
        <td><?echo GetMessage("parser_cat_price_offer")?></td>
        <td><input class="bool-delete" type="checkbox" name="SETTINGS[catalog][cat_vat_price_offer]" value="Y"<?if($kit_SETTINGS["catalog"]["cat_vat_price_offer"] == "Y") echo " checked"?> /></td>
    </tr>
    <?endif;?>
    
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_price_type")?></td>
        <td width="60%"><?=SelectBoxFromArray('SETTINGS[catalog][price_type]', $arPriceType, $kit_SETTINGS["catalog"]["price_type"]?$kit_SETTINGS["catalog"]["price_type"]:1, "", "");?></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_cat_vat_id")?></td>
        <td width="60%"><?=SelectBoxFromArray('SETTINGS[catalog][cat_vat_id]', $arVATRef, $kit_SETTINGS["catalog"]["cat_vat_id"], "", "");?></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_cat_vat_included")?></td>
        <td><input class="bool-delete" type="checkbox" name="SETTINGS[catalog][cat_vat_included]" value="Y"<?if($kit_SETTINGS["catalog"]["cat_vat_included"] == "Y") echo " checked"?> /></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_currency")?></td>
        <td><?=SelectBoxFromArray('SETTINGS[catalog][currency]', $arCurrency, $kit_SETTINGS["catalog"]["currency"]?$kit_SETTINGS["catalog"]["currency"]:"RUB", "", "");?></td>
    </tr>
    
    <tr class="heading adittional_prices_settings">
        <td colspan="2"><?echo GetMessage("parser_more_prices")?></td>
    </tr>
    <?php
    if(isset($kit_SETTINGS["adittional_currency"]) && !empty($kit_SETTINGS["adittional_currency"])){
        foreach($kit_SETTINGS["adittional_currency"] as $id => $currency){
            if(!isset($kit_SETTINGS['prices_preview'][$id])&&!isset($kit_SETTINGS['prices_detail'][$id]))
                continue;
            ?>
            <tr class="adittional_prices_settings_id_<?php echo $id;?>">
                <td>
                    <?echo GetMessage('parser_aditt_currency').' '.$arPricesNames[$id].'['.$id.']';?>
                </td>
                <td>
                    <?=SelectBoxFromArray('SETTINGS[adittional_currency]['.$id.']', $arCurrency, $currency?:"RUB", "", "");?>
                </td>
            </tr>
            <?php
        }
    }
    ?>
    <tr class="heading">
        <td colspan="2"> </td>
    </tr>
    
    <?if(isset($arAllMeasure)):?>
    <tr>
        <td><?echo GetMessage("parser_measure")?></td>
        <td><?=SelectBoxFromArray('SETTINGS[catalog][measure]', $arAllMeasure, $kit_SETTINGS["catalog"]["measure"]?$kit_SETTINGS["catalog"]["measure"]:5, "", "");?></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_catalog_koef")?></td>
        <td><input type="text" name="SETTINGS[catalog][koef]" value="<?echo $kit_SETTINGS["catalog"]["koef"]?$kit_SETTINGS["catalog"]["koef"]:1;?>" size="40" maxlength="250"></td>
    </tr>
    
    <?endif;?>
    
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_store_catalog")?></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_catalog_count_default")?></td>
        <td><input type="text" name="SETTINGS[catalog][count_default]" value="<?echo $kit_SETTINGS["catalog"]["count_default"]?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_catalog_count_default_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_available_quantity")?></td>
        <td>
        <?=SelectBoxFromArray('SETTINGS[store][available_quantity]', $arAmount, isset($kit_SETTINGS["store"]["available_quantity"])?$kit_SETTINGS["store"]["available_quantity"]:'from_file', '', "");?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_catalog_count_do_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_store_list")?></td>
        <td>
        <?=SelectBoxFromArray('SETTINGS[store][list]', $arStore, isset($kit_SETTINGS["store"]["list"])?$kit_SETTINGS["store"]["list"]:GetMessage("parser_not_add_to_store"), GetMessage("parser_not_add_to_store"), "");?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_catalog_stores_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_availability_list")?></td>
    </tr>
    <?php
    if(isset($kit_SETTINGS['availability']['list']) && !empty($kit_SETTINGS['availability']['list'])){
        foreach($kit_SETTINGS['availability']['list'] as $i=>$av){
            ?>
            <tr data-count=<?php echo $i;?>>
                <td><?php echo GetMessage('parser_availability_row').':';?></td>
                <td>
                    <?php echo GetMessage('parser_availability_informer');?>
                    <input type='text' name='SETTINGS[availability][list][<?php echo $i;?>][text]' value="<?php echo $av['text']?>">
                    <?php echo ' - '.GetMessage('parser_availability_count');?>
                    <input type='text' name='SETTINGS[availability][list][<?php echo $i;?>][count]' value="<?php echo $av['count']?>">
                    <a href="#" class='delete_availability_row'>Delete</a>
                </td>
            </tr>
            <?php
        }
    }
    ?>
    <tr>
        <td colspan="2" align="center">
            <input type="submit" id="addAvailabilityRow" name="refresh" value="<?=GetMessage("kit_parser_select_prop_but")?>">
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_availability")?>
            <?=EndNote();?>
        </td>
    </tr>
    
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_work_price")?></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_convert_currency")?></td>
        <td><?=SelectBoxFromArray('SETTINGS[catalog][convert_currency]', $arConvertCurrency, $kit_SETTINGS["catalog"]["convert_currency"], "", "");?></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_price_okrug")?></td>
        <td><?=SelectBoxFromArray('SETTINGS[catalog][price_okrug]', $arPriceOkrug, $kit_SETTINGS["catalog"]["price_okrug"], "", "style=\"width:115px\"");?> <?echo GetMessage("parser_price_okrug_delta1")?> <input type="text" name="SETTINGS[catalog][price_okrug_delta]" value="<?echo $kit_SETTINGS["catalog"]["price_okrug_delta"]?>" size="1" maxlength="1"> <?echo GetMessage("parser_price_okrug_delta2")?> </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_price_okrug_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_price_format")?></td>
        <td><?echo GetMessage("parser_price_format1")?><input type="text" name="SETTINGS[catalog][price_format1]" value="<?echo $kit_SETTINGS["catalog"]["price_format1"]?>" size="1" maxlength="250"><?echo GetMessage("parser_price_format2")?><input type="text" name="SETTINGS[catalog][price_format2]" value="<?echo $kit_SETTINGS["catalog"]["price_format2"]?>" size="1" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_price_format_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?
    $count = count($kit_SETTINGS["catalog"]["price_updown"])-1;
    if(is_set($kit_SETTINGS["catalog"]["price_updown"]) && is_array($kit_SETTINGS["catalog"]["price_updown"]) && count($kit_SETTINGS["catalog"]["price_updown"])>0){
    foreach($kit_SETTINGS["catalog"]["price_updown"] as $i=>$val):
    if($count==$i) $class="tr_add";
    else $class = "";
    ?>
    <tr class="heading <?=$class?>" data-num="<?=($i+1)?>">
        <td colspan="2"><?echo GetMessage("parser_work_price_num")?> <span><?=($i+1)?></span> <?if($count==$i):?><a href="#" style="font-size:12px;" class="add_usl"><?echo GetMessage("parser_price_num_add")?></a><?endif;?>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" style="font-size:12px;<?if($i==0):?>display:none<?endif;?>" class="del_usl"><?echo GetMessage("parser_price_num_del")?></a></td>
    </tr>
    <tr class="<?=$class?>">
        <td><?echo GetMessage("parser_price_updown")?></td>
        <td>
        <?=SelectBoxFromArray('SETTINGS[catalog][price_updown][]', $arPriceUpDown, $kit_SETTINGS["catalog"]["price_updown"][$i], "", "");?><?=GetMessage("parser_updown_section_dop_desc");?>
        <?=SelectBoxFromArray('SETTINGS[catalog][price_updown_section_dop][]', $arDopUrl, $kit_SETTINGS["catalog"]["price_updown_section_dop"][$i], "", "");?>
        </td>
    </tr>
    <tr class="<?=$class?>">
        <td><?echo GetMessage("parser_price_terms")?></td>
        <td><?=SelectBoxFromArray('SETTINGS[catalog][price_terms][]', $arPriceTerms, $kit_SETTINGS["catalog"]["price_terms"][$i], "", "");?> <?echo GetMessage("parser_price_from")?> <input type="text" name="SETTINGS[catalog][price_terms_value][]" value="<?echo $kit_SETTINGS["catalog"]["price_terms_value"][$i];?>" size="10" maxlength="250"> <?echo GetMessage("parser_price_to")?> <input type="text" name="SETTINGS[catalog][price_terms_value_to][]" value="<?echo $kit_SETTINGS["catalog"]["price_terms_value_to"][$i];?>" size="10" maxlength="250"></td>
    </tr>
    <tr class="<?=$class?>">
        <td><?echo GetMessage("parser_price_type_value")?></td>
        <td><?=SelectBoxFromArray('SETTINGS[catalog][price_type_value][]', $arPriceValue, $kit_SETTINGS["catalog"]["price_type_value"][$i], "", "");?></td>
    </tr>
    <tr class="<?=$class?> <?if($class):?>tr_last<?endif;?>">
        <td><?echo GetMessage("parser_price_value")?></td>
        <td><input type="text" name="SETTINGS[catalog][price_value][]" value="<?echo $kit_SETTINGS["catalog"]["price_value"][$i];?>" size="10" maxlength="250"></td>
    </tr>
    <?endforeach;
    }else{
    ?>
    <tr class="heading tr_add" data-num="1">
        <td colspan="2"><?echo GetMessage("parser_work_price_num")?> <span></span> <a href="#" style="font-size:12px;" class="add_usl"><?echo GetMessage("parser_price_num_add")?></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" style="font-size:12px;display:none" class="del_usl"><?echo GetMessage("parser_price_num_del")?></a></td>
    </tr>
    <tr class="tr_add">
        <td><?echo GetMessage("parser_price_updown")?></td>
        <td><?=SelectBoxFromArray('SETTINGS[catalog][price_updown][]', $arPriceUpDown, $kit_SETTINGS["catalog"]["price_updown"], "", "");?></td>
    </tr>
    <tr class="tr_add">
        <td><?echo GetMessage("parser_price_terms")?></td>
        <td><?=SelectBoxFromArray('SETTINGS[catalog][price_terms][]', $arPriceTerms, $kit_SETTINGS["catalog"]["price_terms"], "", "");?> <?echo GetMessage("parser_price_from")?> <input type="text" name="SETTINGS[catalog][price_terms_value][]" value="<?echo $kit_SETTINGS["catalog"]["price_terms_value"];?>" size="10" maxlength="250"> <?echo GetMessage("parser_price_to")?> <input type="text" name="SETTINGS[catalog][price_terms_value_to][]" value="<?echo $kit_SETTINGS["catalog"]["price_terms_value_to"];?>" size="10" maxlength="250"></td>
    </tr>
    <tr class="tr_add">
        <td><?echo GetMessage("parser_price_type_value")?></td>
        <td><?=SelectBoxFromArray('SETTINGS[catalog][price_type_value][]', $arPriceValue, $kit_SETTINGS["catalog"]["price_type_value"], "", "");?></td>
    </tr>
    <tr class="tr_add tr_last">
        <td><?echo GetMessage("parser_price_value")?></td>
        <td><input type="text" name="SETTINGS[catalog][price_value][]" value="<?echo $kit_SETTINGS["catalog"]["price_value"];?>" size="10" maxlength="250"></td>
    </tr>
    
    <?}?>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_size_selector")?></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_size_length")?></td>
        <td><input type="text" name="SETTINGS[catalog][selector_product][LENGTH]" value="<?echo $kit_SETTINGS["catalog"]["selector_product"]["LENGTH"];?>" size="40" maxlength="250"> X <input type="text" name="SETTINGS[catalog][selector_product_koef][LENGTH]" value="<?echo $kit_SETTINGS["catalog"]["selector_product_koef"]["LENGTH"];?>" size="10" maxlength="250"></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_size_width")?></td>
        <td><input type="text" name="SETTINGS[catalog][selector_product][WIDTH]" value="<?echo $kit_SETTINGS["catalog"]["selector_product"]["WIDTH"];?>" size="40" maxlength="250"> X <input type="text" name="SETTINGS[catalog][selector_product_koef][WIDTH]" value="<?echo $kit_SETTINGS["catalog"]["selector_product_koef"]["WIDTH"];?>" size="10" maxlength="250"></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_size_height")?></td>
        <td><input type="text" name="SETTINGS[catalog][selector_product][HEIGHT]" value="<?echo $kit_SETTINGS["catalog"]["selector_product"]["HEIGHT"];?>" size="40" maxlength="250"> X <input type="text" name="SETTINGS[catalog][selector_product_koef][HEIGHT]" value="<?echo $kit_SETTINGS["catalog"]["selector_product_koef"]["HEIGHT"];?>" size="10" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_size_weight")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][selector_product][WEIGHT]" value="<?echo $kit_SETTINGS["catalog"]["selector_product"]["WEIGHT"];?>" size="40" maxlength="250"> X <input type="text" name="SETTINGS[catalog][selector_product_koef][WEIGHT]" value="<?echo $kit_SETTINGS["catalog"]["selector_product_koef"]["WEIGHT"];?>" size="10" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_catalog_delete_symb")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][catalog_delete_selector_symb]" value="<?echo $kit_SETTINGS["catalog"]["catalog_delete_selector_symb"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_selector_size_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_size_find")?></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_selector_find")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][selector_find_size]" value="<?echo $kit_SETTINGS["catalog"]["selector_find_size"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_size_length")?></td>
        <td><input type="text" name="SETTINGS[catalog][find_product][LENGTH]" value="<?echo $kit_SETTINGS["catalog"]["find_product"]["LENGTH"];?>" size="40" maxlength="250"> X <input type="text" name="SETTINGS[catalog][find_product_koef][LENGTH]" value="<?echo $kit_SETTINGS["catalog"]["find_product_koef"]["LENGTH"];?>" size="10" maxlength="250"></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_size_width")?></td>
        <td><input type="text" name="SETTINGS[catalog][find_product][WIDTH]" value="<?echo $kit_SETTINGS["catalog"]["find_product"]["WIDTH"];?>" size="40" maxlength="250"> X <input type="text" name="SETTINGS[catalog][find_product_koef][WIDTH]" value="<?echo $kit_SETTINGS["catalog"]["find_product_koef"]["WIDTH"];?>" size="10" maxlength="250"></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_size_height")?></td>
        <td><input type="text" name="SETTINGS[catalog][find_product][HEIGHT]" value="<?echo $kit_SETTINGS["catalog"]["find_product"]["HEIGHT"];?>" size="40" maxlength="250"> X <input type="text" name="SETTINGS[catalog][find_product_koef][HEIGHT]" value="<?echo $kit_SETTINGS["catalog"]["find_product_koef"]["HEIGHT"];?>" size="10" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_size_weight")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][find_product][WEIGHT]" value="<?echo $kit_SETTINGS["catalog"]["find_product"]["WEIGHT"];?>" size="40" maxlength="250"> X <input type="text" name="SETTINGS[catalog][find_product_koef][WEIGHT]" value="<?echo $kit_SETTINGS["catalog"]["find_product_koef"]["WEIGHT"];?>" size="10" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_catalog_delete_symb")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][catalog_delete_find_symb]" value="<?echo $kit_SETTINGS["catalog"]["catalog_delete_find_symb"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_find_size_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
<?
endif;
//offers
if(!$hideCatalog):
$tabControl->BeginNextTab();
?>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_load_preview_or_detail")?></td> <!--Парсить со страницы каталога:-->
        <td width="60%"><input type="checkbox" name="SETTINGS[offer][preview_or_detail]" value="Y"<?if($kit_SETTINGS["offer"]["preview_or_detail"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_load_preview_or_detail_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_load")?></td>
        <td width="60%"><?=SelectBoxFromArray('SETTINGS[offer][load]', $arOfferLoad, $kit_SETTINGS["offer"]["load"]?$kit_SETTINGS["offer"]["load"]:1, "", "");?></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_load_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?if(isset($kit_SETTINGS["offer"]["load"]) && $kit_SETTINGS["offer"]["load"]=="one"):?>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_one_selector")?>:</td> <!--Селектор контейнера отдельной характеристики оффера-->
        <td width="60%"><input type="text" name="SETTINGS[offer][one][selector]" value="<?echo $kit_SETTINGS["offer"]['one']["selector"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_one_selector_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_one_detail_img")?>:</td>
        <td width="60%"><input type="text" name="SETTINGS[offer][one][detail_img]" value="<?echo $kit_SETTINGS["offer"]['one']["detail_img"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_one_detail_img_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_one_price_attr")?>:</td>
        <td width="60%"><input type="text" name="SETTINGS[offer][one][price]" value="<?echo $kit_SETTINGS["offer"]['one']["price"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_one_price_attr_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_one_quantity_attr")?>:</td>
        <td width="60%"><input type="text" name="SETTINGS[offer][one][quantity]" value="<?echo $kit_SETTINGS["offer"]['one']["quantity"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_one_quantity_attr_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_add_name")?></td>
        <td width="60%">
        <?if(isset($arrPropDopOfferName)):?>
            <select name="SETTINGS[offer][add_name][]" class="add_name">
                <?foreach($arrPropDopOfferName["REFERENCE"] as $r=>$ref):?>
                <option <?if(is_array($kit_SETTINGS["offer"]["add_name"]) && in_array($arrPropDopOfferName["REFERENCE_ID"][$r], $kit_SETTINGS["offer"]["add_name"])):?>selected=""<?endif;?> value="<?=$arrPropDopOfferName["REFERENCE_ID"][$r]?>"><?=$ref?></option>
                <?endforeach?>
            </select>
        <?endif;?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_add_name_one_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr> <!--Данное поле для выгрузки офферов с одной характеристикой-->
        <td class="field-name" width="40%"><?echo GetMessage("parser_catalog_delete_symb")?></td>
        <td width="60%"><input type="text" name="SETTINGS[offer][catalog_delete_selector_props_symb]" value="<?echo $kit_SETTINGS["offer"]["catalog_delete_selector_props_symb"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_delete_symb_descr_offer")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?endif;?>
    <?if(isset($kit_SETTINGS["offer"]["load"]) && $kit_SETTINGS["offer"]["load"]=="more"):?>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_add_name")?></td>
        <td width="60%">
        <?if(isset($arrPropDopOfferName)):?>
            <select name="SETTINGS[offer][add_name][]" multiple="" class="add_name">
                <?foreach($arrPropDopOfferName["REFERENCE"] as $r=>$ref):?>
                <option <?if(is_array($kit_SETTINGS["offer"]["add_name"]) && in_array($arrPropDopOfferName["REFERENCE_ID"][$r], $kit_SETTINGS["offer"]["add_name"])):?>selected=""<?endif;?> value="<?=$arrPropDopOfferName["REFERENCE_ID"][$r]?>"><?=$ref?></option>
                <?endforeach?>
            </select>
        <?endif;?>
        </td>
    </tr>
   <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_add_name_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_offer_more_props_descr")?></td>  <!--Свойства торгового предложения-->
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_catalog_delete_symb")?></td>
        <td width="60%"><input type="text" name="SETTINGS[offer][catalog_delete_selector_props_symb]" value="<?echo $kit_SETTINGS["offer"]["catalog_delete_selector_props_symb"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_delete_symb_descr_offer")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_selector_table")?></td>
        <td width="60%"><input type="text" name="SETTINGS[offer][catalog_offer_selector_table]" value="<?echo $kit_SETTINGS["offer"]["catalog_offer_selector_table"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_selector_table_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?if(isset($kit_SETTINGS["offer"]["selector_prop_more"]) && !empty($kit_SETTINGS["offer"]["selector_prop_more"])):?>
    <?foreach($kit_SETTINGS["offer"]["selector_prop_more"] as $code=>$val):
//        $val = trim($val);
        if(!$val) continue 1;
    ?>
    <tr>
        <td width="40%"><?=$arrPropDopOffer['REFERENCE_CODE_NAME'][$code]?>&nbsp;[<?=$code?>]:</td>
        <td width="60%">
            <input type="text" size="40" data-code="<?=$code?>" name="SETTINGS[offer][selector_prop_more][<?=$code?>]" value="<?=$kit_SETTINGS["offer"]["selector_prop_more"][$code]?>">
            <a class="prop_delete" href="#">Delete</a>
        </td>
    </tr>
    <?endforeach?>
    <?endif;?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPropDopOffer', $arrPropDopOffer, "", GetMessage("kit_parser_select_prop"), "");?>
            <input type="submit" id="loadDopPropOffer2" name="refresh" value="<?=GetMessage("kit_parser_select_prop_but")?>">
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_offer_more_add_name_heading")?></td> <!--Название торгового предложения-->
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_more_add_name_notes")?></td>
        <td width="60%"><input type="text" name="SETTINGS[offer][add_offer_name_more]" value="<?echo $kit_SETTINGS["offer"]["add_offer_name_more"];?>" size="40" maxlength="250"></td>
    </tr>
   <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_more_add_name_desc")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?endif;?>
    <?if(isset($kit_SETTINGS["offer"]["load"]) && $kit_SETTINGS["offer"]["load"]=="table"):?>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_add_name")?></td>
        <td width="60%">
        <?if(isset($arrPropDopOfferName)):?>
            <select name="SETTINGS[offer][add_name][]" multiple="" class="add_name">
                <?foreach($arrPropDopOfferName["REFERENCE"] as $r=>$ref):?>
                <option <?if(is_array($kit_SETTINGS["offer"]["add_name"]) && in_array($arrPropDopOfferName["REFERENCE_ID"][$r], $kit_SETTINGS["offer"]["add_name"])):?>selected=""<?endif;?> value="<?=$arrPropDopOfferName["REFERENCE_ID"][$r]?>"><?=$ref?></option>
                <?endforeach?>
            </select>
        <?endif;?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_add_name_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_offer_table_desc")?></td>
    </tr><?/*?>
    <tr class="heading">
        <td colspan="2">
        <?/*=BeginNote();?>
        <table>
            <thead>
                <tr>
                    <th>
                        <?echo GetMessage("parser_offer_table_desc_1")?>
                    </th>
                    <th><?echo GetMessage("parser_offer_table_desc_2")?></th>
                    <th><?echo GetMessage("parser_offer_table_desc_3")?></th>
                    <th><?echo GetMessage("parser_offer_table_desc_4")?></th>
                    <th><?echo GetMessage("parser_offer_table_desc_5")?></th>
                    <th><?echo GetMessage("parser_offer_table_desc_6")?></th>
                    <th><?echo GetMessage("parser_offer_table_desc_7")?></th>
                    <th></th>
                    <th>
                            <?echo GetMessage("parser_offer_table_desc_8")?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr class="item_row">
                    <td>976886</td>
                    <td><?echo GetMessage("parser_offer_table_desc_9")?></td>
                    <td><?echo GetMessage("parser_offer_table_desc_12")?></td>
                    <td>416</td>
                    <td>8(RUS-GB-F-E-ARAB-D-H-PL)</td>
                    <td><?echo GetMessage("parser_offer_table_desc_10")?></td>
                    <td><?echo GetMessage("parser_offer_table_desc_11")?></td>
                    <td></td>
                    <td>100</td>
                </tr>
                <tr class="item_row">
                    <td>976886</td>
                    <td><?echo GetMessage("parser_offer_table_desc_9")?></td>
                    <td><?echo GetMessage("parser_offer_table_desc_12")?></td>
                    <td>416</td>
                    <td>8(RUS-GB-F-E-ARAB-D-H-PL)</td>
                    <td><?echo GetMessage("parser_offer_table_desc_10")?></td>
                    <td><?echo GetMessage("parser_offer_table_desc_11")?></td>
                    <td></td>
                    <td>100</td>
                </tr>
                <tr class="item_row">
                    <td>976886</td>
                    <td><?echo GetMessage("parser_offer_table_desc_9")?></td>
                    <td><?echo GetMessage("parser_offer_table_desc_12")?></td>
                    <td>416</td>
                    <td>8(RUS-GB-F-E-ARAB-D-H-PL)</td>
                    <td><?echo GetMessage("parser_offer_table_desc_10")?></td>
                    <td><?echo GetMessage("parser_offer_table_desc_11")?></td>
                    <td></td>
                    <td>100</td>
                </tr>
                </tbody>
            </table>
            <?=EndNote();*/?>
        <?/*?></td>
    </tr><?*/?>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_selector")?></td>
        <td width="60%"><input type="text" name="SETTINGS[offer][selector]" value="<?echo $kit_SETTINGS["offer"]["selector"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_selector_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_selector_head")?></td>
        <td width="60%"><input type="text" name="SETTINGS[offer][selector_head]" value="<?echo $kit_SETTINGS["offer"]["selector_head"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_selector_head_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_selector_head_th")?></td>
        <td width="60%"><input type="text" name="SETTINGS[offer][selector_head_th]" value="<?echo $kit_SETTINGS["offer"]["selector_head_th"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_selector_head_th_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_selector_item")?></td>
        <td width="60%"><input type="text" name="SETTINGS[offer][selector_item]" value="<?echo $kit_SETTINGS["offer"]["selector_item"];?>" size="40" maxlength="250"></td>
    </tr>
    
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_selector_item_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_selector_item_td")?></td>
        <td width="60%"><input type="text" name="SETTINGS[offer][selector_item_td]" value="<?echo $kit_SETTINGS["offer"]["selector_item_td"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_selector_item_td_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_offer_parsing_selector")?></td> <!--Парсинг полей и свойств по селектору-->
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_catalog_delete_symb")?></td>
        <td width="60%"><input type="text" name="SETTINGS[offer][catalog_delete_selector_props_symb]" value="<?echo $kit_SETTINGS["offer"]["catalog_delete_selector_props_symb"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_delete_symb_descr_offer")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_parsing_selector_name")?></td>
        <td width="60%"><input type="text" name="SETTINGS[offer][selector_name]" value="<?echo $kit_SETTINGS["offer"]["selector_name"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_parsing_selector_name_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_parsing_selector_price")?></td>
        <td width="60%"><input type="text" name="SETTINGS[offer][selector_price]" value="<?echo $kit_SETTINGS["offer"]["selector_price"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_parsing_selector_price_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_more_prices")?></td>
    </tr>
    <?php
    if(isset($kit_SETTINGS["offer"]["selector_additional_prices"])&&!empty($kit_SETTINGS["offer"]["selector_additional_prices"])){
        foreach($kit_SETTINGS["offer"]["selector_additional_prices"] as $id_price => $price){
        ?>
        <tr class="offer_additional_prices_id_<?php echo $id_price;?>">
            <td><?php echo GetMessage('parser_preview_price').$price['name'].'['.$id_price.']:';?></td>
            <td>
                <input type="text" name="SETTINGS[offer][selector_additional_prices][<?php echo $id_price;?>][value]" value="<?echo $price["value"];?>" size="40" maxlength="250">&nbsp;<a href="#" class="price_delete" data-price-id="<?php echo $id_price;?>">Delete</a>
                <input type="hidden" name="SETTINGS[offer][selector_additional_prices][<?php echo $id_price;?>][name]" value="<?echo $price["name"];?>">
            </td>
        </tr>
        <?php
        }
    }
    ?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPricetypes', $arAdditPriceType, "", GetMessage("kit_parser_addit_prices"), "");?>
            <input type="submit" id="addPriceOffer" name="refresh" value="<?=GetMessage("kit_parser_add_addit_prices")?>">
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"> </td>
    </tr>
    
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_parsing_selector_quantity")?></td>
        <td width="60%"><input type="text" name="SETTINGS[offer][selector_quantity]" value="<?echo $kit_SETTINGS["offer"]["selector_quantity"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_parsing_selector_quantity_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?if(isset($kit_SETTINGS["offer"]["selector_prop"]) && !empty($kit_SETTINGS["offer"]["selector_prop"])):?>
    <?foreach($kit_SETTINGS["offer"]["selector_prop"] as $code=>$val):
//        $val = trim($val);
        if(!$val) continue 1;
    ?>
    <tr>
        <td width="40%"><?=$arrPropDopOffer['REFERENCE_CODE_NAME'][$code]?>&nbsp;[<?=$code?>]:</td>
        <td width="60%">
            <input type="text" size="40" data-code="<?=$code?>" name="SETTINGS[offer][selector_prop][<?=$code?>]" value="<?=$kit_SETTINGS["offer"]["selector_prop"][$code]?>">
            <a class="prop_delete" href="#">Delete</a>
        </td>
    </tr>
    <?endforeach?>
    <?endif;?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPropDopOffer', $arrPropDopOffer, "", GetMessage("kit_parser_select_prop"), "");?>
            <input type="submit" id="loadDopPropOffer" name="refresh" value="<?=GetMessage("kit_parser_select_prop_but")?>">
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_offer_parsing_find")?></td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"><?echo GetMessage("parser_catalog_delete_symb")?></td>
        <td width="60%"><input type="text" name="SETTINGS[offer][catalog_delete_selector_find_props_symb]" value="<?echo $kit_SETTINGS["offer"]["catalog_delete_selector_find_props_symb"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_delete_symb_descr_offer")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_parsing_selector_name")?></td>
        <td width="60%"><input type="text" name="SETTINGS[offer][find_name]" value="<?echo $kit_SETTINGS["offer"]["find_name"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_parsing_selector_name_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_parsing_selector_price")?></td>
        <td width="60%"><input type="text" name="SETTINGS[offer][find_price]" value="<?echo $kit_SETTINGS["offer"]["find_price"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_parsing_selector_price_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_more_prices")?></td>
    </tr>
    <?php
    if(isset($kit_SETTINGS["offer"]["find_price"])&&!empty($kit_SETTINGS["offer"]["find_price"])){
        foreach($kit_SETTINGS["offer"]["find_price"] as $id_price => $price){
        ?>
        <tr class="offer_additional_prices_name_id_<?php echo $id_price;?>">
            <td><?php echo GetMessage('parser_preview_price').$price['name'].'['.$id_price.']:';?></td>
            <td>
                <input type="text" name="SETTINGS[offer][find_price][<?php echo $id_price;?>][value]" value="<?echo $price["value"];?>" size="40" maxlength="250">&nbsp;<a href="#" class="price_delete" data-price-id="<?php echo $id_price;?>">Delete</a>
                <input type="hidden" name="SETTINGS[offer][find_price][<?php echo $id_price;?>][name]" value="<?echo $price["name"];?>">
            </td>
        </tr>
        <?php
        }
    }
    ?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPricetypes', $arAdditPriceType, "", GetMessage("kit_parser_addit_prices"), "");?>
            <input type="submit" id="addNamePriceOffer" name="refresh" value="<?=GetMessage("kit_parser_add_addit_prices")?>">
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"> </td>
    </tr>
    
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_parsing_selector_quantity")?></td>
        <td width="60%"><input type="text" name="SETTINGS[offer][find_quantity]" value="<?echo $kit_SETTINGS["offer"]["find_quantity"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_parsing_selector_quantity_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?if(isset($kit_SETTINGS["offer"]["find_prop"]) && !empty($kit_SETTINGS["offer"]["find_prop"])):?>
    <?foreach($kit_SETTINGS["offer"]["find_prop"] as $code=>$val):
//        $val = trim($val);
        if(!$val) continue 1;
    ?>
    <tr>
        <td width="40%"><?=$arrPropDopOffer['REFERENCE_CODE_NAME'][$code]?>&nbsp;[<?=$code?>]:</td>
        <td width="60%">
            <input type="text" size="40" data-code="<?=$code?>" name="SETTINGS[offer][find_prop][<?=$code?>]" value="<?=$kit_SETTINGS["offer"]["find_prop"][$code]?>">
            <a class="prop_delete" href="#">Delete</a>
        </td>
    </tr>
    <?endforeach?>
    <?endif;?>
    
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPropDopOffer', $arrPropDopOffer, "", GetMessage("kit_parser_select_prop"), "");?>
            <input type="submit" id="loadDopPropOffer1" name="refresh" value="<?=GetMessage("kit_parser_select_prop_but")?>">
        </td>
    </tr>
    <?endif;?>
    
<?

endif;
//Adittional settings
$tabControl->BeginNextTab();
?>
    <tr>
        <td width="40%"><?echo GetMessage("parser_active_element")?></td>
        <td width="60%"><input type="checkbox" name="ACTIVE_ELEMENT" value="Y"<?if($kit_ACTIVE_ELEMENT == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_code_element")?></td>
        <td width="60%"><input type="checkbox" name="CODE_ELEMENT" value="Y"<?if($kit_CODE_ELEMENT == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_index_element")?></td>
        <td width="60%"><input type="checkbox" name="INDEX_ELEMENT" value="Y"<?if($kit_INDEX_ELEMENT == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_resize_image")?></td>
        <td width="60%"><input type="checkbox" name="RESIZE_IMAGE" value="Y"<?if($kit_RESIZE_IMAGE == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_preview_from_detail")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][img_preview_from_detail]" value="Y"<?if($kit_SETTINGS["catalog"]["img_preview_from_detail"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_preview_from_detail_text")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][text_preview_from_detail]" value="Y"<?if($kit_SETTINGS["catalog"]["text_preview_from_detail"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_404_error")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][404]" value="Y"<?if($kit_SETTINGS["catalog"]["404"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_404_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_date_active")?></td>
        <td width="60%"><input type="checkbox" name="DATE_ACTIVE" value="Y"<?if($kit_DATE_ACTIVE && $kit_DATE_ACTIVE != "N") echo " checked"?>> <?=SelectBoxFromArray('DATE_PROP_ACTIVE', $arrDateActive, $kit_DATE_ACTIVE, GetMessage("parser_date_type"), "id='prop-active' style='width:262px'");?></td>
    </tr>
    <?/*?><tr>
        <td width="40%"><?echo GetMessage("parser_date_public")?></td>
        <td width="60%"><input type="checkbox" name="DATE_PUBLIC" value="Y"<?if($kit_DATE_PUBLIC && $kit_DATE_PUBLIC != "N") echo " checked"?>> <?=SelectBoxFromArray('DATE_PROP_PUBLIC', $arrProp, $kit_DATE_PUBLIC, GetMessage("parser_prop_id"), "id='prop-date' style='width:262px' class='prop-iblock'");?></td>
    </tr><?*/?>
    <tr>
        <td width="40%"><?echo GetMessage("parser_first_title")?></td>
        <td width="60%"><input type="checkbox" name="FIRST_TITLE" value="Y"<?if($kit_FIRST_TITLE && $kit_FIRST_TITLE != "N") echo " checked"?>> <?=SelectBoxFromArray('FIRST_PROP_TITLE', $arrProp, $kit_FIRST_TITLE, GetMessage("parser_prop_id"), "id='prop-first' style='width:262px' class='prop-iblock'");?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_meta_title")?></td>
        <td width="60%"><input type="checkbox" name="META_TITLE" value="Y"<?if($kit_META_TITLE && $kit_META_TITLE != "N") echo " checked"?>> <?=SelectBoxFromArray('META_PROP_TITLE', $arrProp, $kit_META_TITLE, GetMessage("parser_prop_id"), "id='prop-title' style='width:262px' class='prop-iblock'");?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_meta_description")?></td>
        <td width="60%"><input type="checkbox" name="META_DESCRIPTION" value="Y"<?if($kit_META_DESCRIPTION && $kit_META_DESCRIPTION != "N") echo " checked"?>> <?=SelectBoxFromArray('META_PROP_DESCRIPTION', $arrProp, $kit_META_DESCRIPTION, GetMessage("parser_prop_id"), "id='prop-key' style='width:262px' class='prop-iblock'");?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_meta_keywords")?></td>
        <td width="60%"><input type="checkbox" name="META_KEYWORDS" value="Y"<?if($kit_META_KEYWORDS && $kit_META_KEYWORDS != "N") echo " checked"?>> <?=SelectBoxFromArray('META_PROP_KEYWORDS', $arrProp, $kit_META_KEYWORDS, GetMessage("parser_prop_id"), "id='prop-meta' style='width:262px' class='prop-iblock'");?></td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?php echo GetMessage('parser_start_header');?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_start_agent")?></td>
        <td width="60%"><input type="checkbox" name="START_AGENT" value="Y"<?if($kit_START_AGENT == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_start_agent_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_time_agent")?></td>
        <td width="60%"><input type="text" size="40" name="TIME_AGENT" value="<?=$kit_TIME_AGENT?>"></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_sleep")?></td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[catalog][sleep]" value="<?=$kit_SETTINGS["catalog"]["sleep"]?>"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_sleep_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?php echo GetMessage('parser_proxy_header');?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_proxy").':'?></td>
        <td width="60%">
            <input type="text" size="40" name="SETTINGS[catalog][proxy]" value="<?=$kit_SETTINGS["catalog"]["proxy"]?>">
            <input placeholder="username:password" type="text" size="30" name="SETTINGS[proxy][username_password]" value="<?=$kit_SETTINGS["proxy"]["username_password"]?>">
        </td>
    </tr>
    <?php
    if(isset($kit_SETTINGS['proxy']['servers']) && !empty($kit_SETTINGS['proxy']['servers'])){
    $i = 1;
        foreach($kit_SETTINGS['proxy']['servers'] as $id => $server){
            if(empty($server))
                continue;
            ?>
            <tr data-id="<?php echo $id?>">
                <td><?php echo GetMessage('parser_proxy').' '.$i.':';?></td>
                <td><input type="text"  size="40" name="SETTINGS[proxy][servers][<?php echo $id?>][ip]" value="<?php echo $server['ip'];?>"> <input placeholder="username:password" type="text" size="30" name="SETTINGS[proxy][servers][<?php echo $id?>][username_password]" value="<?=$server["username_password"]?>"> <a href="#" class="delete_proxy_server"><?php echo GetMessage('delete');?></a></td>
            </tr>
            <?php
            $i++;
        }
    }
    ?>
    <tr>
        <td colspan="2" align="center">
            <input type="submit" id="addProxyServer" name="refresh" value="<? echo GetMessage('add_proxy_server')?>">
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_proxy_username_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_madialibrary")?></td>
        <td width="60%"><?=SelectBoxFromArray('SETTINGS[madialibrary_id]', $arrLibrary, $kit_SETTINGS["madialibrary_id"], GetMessage("parser_no_select"), "");?></td>
    </tr>
<?
$tabControl->BeginNextTab();
?>
    <tr>
        <td width="40%"><?echo GetMessage("parser_uniq_update")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][active]" value="Y"<?if($kit_SETTINGS["catalog"]["update"]["active"] == "Y") echo " checked"?>></td>
    </tr>
    
    <tr class="show_block_add_element" <?if(!isset($kit_SETTINGS["catalog"]["update"]["active"]) || ($kit_SETTINGS["catalog"]["update"]["active"] != "Y")):?>style="display: none"<?endif;?>>
        <td width="40%"><?echo GetMessage("parser_uniq_add_element")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][add_element]" value="Y"<?if($kit_SETTINGS["catalog"]["update"]["add_element"] == "Y") echo " checked"?>></td>
    </tr>
    <tr class="show_block_add_element" <?if(!isset($kit_SETTINGS["catalog"]["update"]["active"]) || ($kit_SETTINGS["catalog"]["update"]["active"] != "Y")):?>style="display: none"<?endif;?>>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_uniq_add_element_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_header_uniq")?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_uniq_name")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][uniq][name]" value="Y"<?if($kit_SETTINGS["catalog"]["uniq"]["name"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_uniq_prop")?></td>
        <td width="60%"><?=SelectBoxFromArray('SETTINGS[catalog][uniq][prop]', $arrProp, $kit_SETTINGS["catalog"]["uniq"]["prop"], GetMessage("parser_prop_id"), "id='style='width:262px' class='prop-iblock'");?></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_uniq_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_header_uniq_field")?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_name")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][name]" value="Y"<?if($kit_SETTINGS["catalog"]["update"]["name"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_activate")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][activate]" value="Y"<?if($kit_SETTINGS["catalog"]["update"]["activate"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_price")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][price]" value="Y"<?if($kit_SETTINGS["catalog"]["update"]["price"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_count")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][count]" value="Y"<?if($kit_SETTINGS["catalog"]["update"]["count"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_param")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][param]" value="Y"<?if($kit_SETTINGS["catalog"]["update"]["param"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_preview_descr")?></td>
        <td width="60%">
            <?=SelectBoxFromArray('SETTINGS[catalog][update][preview_descr]', $arUpdate, $kit_SETTINGS["catalog"]["update"]["preview_descr"], "", "");?>
            <?/*?><input type="checkbox" name="SETTINGS[catalog][update][preview_descr]" value="Y"<?if($kit_SETTINGS["catalog"]["update"]["preview_descr"] == "Y") echo " checked"?>><?*/?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_detail_descr")?></td>
        <td width="60%">
            <?=SelectBoxFromArray('SETTINGS[catalog][update][detail_descr]', $arUpdate, $kit_SETTINGS["catalog"]["update"]["detail_descr"], "", "");?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_preview_img")?></td>
        <td width="60%">
            <?=SelectBoxFromArray('SETTINGS[catalog][update][preview_img]', $arUpdate, $kit_SETTINGS["catalog"]["update"]["preview_img"], "", "");?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_detail_img")?></td>
        <td width="60%">
            <?=SelectBoxFromArray('SETTINGS[catalog][update][detail_img]', $arUpdate, $kit_SETTINGS["catalog"]["update"]["detail_img"], "", "");?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_more_img")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][more_img]" value="Y"<?if($kit_SETTINGS["catalog"]["update"]["more_img"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_props")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][props]" value="Y"<?if($kit_SETTINGS["catalog"]["update"]["props"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_header_uniq_field_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_header_element_action")?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_element_action")?></td>
        <td width="60%">
            <?=SelectBoxFromArray('SETTINGS[catalog][uniq][action]', $arAction, $kit_SETTINGS["catalog"]["uniq"]["action"], "", "");?>
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_element_action_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?
    //Autorization
    $tabControl->BeginNextTab();
    ?>
    <tr>
        <td width="40%"><?echo GetMessage("parser_auth_type")?></td>
        <td width="60%">
            <?=SelectBoxFromArray('SETTINGS[catalog][auth][type]', $arAuthType, $kit_SETTINGS["catalog"]["auth"]["type"], "", "class='select_load'");?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_auth_active")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][auth][active]" value="Y"<?if($kit_SETTINGS["catalog"]["auth"]["active"] == "Y") echo " checked"?>></td>
    </tr>
    <?if((isset($kit_SETTINGS["catalog"]["auth"]["type"]) && $kit_SETTINGS["catalog"]["auth"]["type"]=="form") || !isset($kit_SETTINGS["catalog"]["auth"]["type"])):?>
    <tr>
        <td width="40%"><?echo GetMessage("parser_auth_url")?></td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[catalog][auth][url]" value="<?=$kit_SETTINGS["catalog"]["auth"]["url"]?>"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_auth_url_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_auth_selector")?></td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[catalog][auth][selector]" value="<?=$kit_SETTINGS["catalog"]["auth"]["selector"]?>"></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_auth_login")?></td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[catalog][auth][login]" value="<?=$kit_SETTINGS["catalog"]["auth"]["login"]?>"> <?echo GetMessage("parser_auth_login_name")?> <input type="text" size="20" name="SETTINGS[catalog][auth][login_name]" value="<?=$kit_SETTINGS["catalog"]["auth"]["login_name"]?>"></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_auth_password")?></td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[catalog][auth][password]" value="<?=$kit_SETTINGS["catalog"]["auth"]["password"]?>"> <?echo GetMessage("parser_auth_password_name")?> <input type="text" size="20" name="SETTINGS[catalog][auth][password_name]" value="<?=$kit_SETTINGS["catalog"]["auth"]["password_name"]?>"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_auth_password_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?else:?>
    <tr>
        <td width="40%"><?echo GetMessage("parser_auth_login")?></td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[catalog][auth][login]" value="<?=$kit_SETTINGS["catalog"]["auth"]["login"]?>"></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_auth_password")?></td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[catalog][auth][password]" value="<?=$kit_SETTINGS["catalog"]["auth"]["password"]?>"></td>
    </tr>
    <?endif;?>
    <?if($kit_SETTINGS["catalog"]["auth"]["type"]=="form"):?>
    <tr>
        <td width="40%"></td>
        <td width="60%"><input type="button" size="40" id="auth" name="auth" data-href="<?=$APPLICATION->GetCurPageParam("auth=1", array("auth")); ?>" value="<?echo GetMessage('parser_auth_check')?>"></td>
    </tr>
    <?endif;?>
    <?
    $tabControl->BeginNextTab();
    ?>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_logs")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][log]" value="Y"<?if($kit_SETTINGS["catalog"]["log"] == "Y") echo " checked"?>></td>
    </tr>
    <?
    $file_log = $_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/kit.parser/include/catalog_log_".htmlspecialcharsbx($_GET["ID"]).".txt";
    if(isset($_GET["ID"]) && file_exists($file_log)):?>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_logs_download")?></td>
        <td width="60%"><a href="<?=$APPLICATION->GetCurPageParam("log_ID=".htmlspecialcharsbx($_GET["ID"]), array("log_ID"));?>">catalog_log_<?=htmlspecialcharsbx($_GET["ID"])?>.txt  (<?=htmlspecialcharsbx(ceil(filesize($file_log)/1024))?> KB)</a></td>
    </tr>
    <?endif?>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_header_log_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_smart_logs_head")?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_smart_logs")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[smart_log][enabled]" value="Y"<?if($kit_SETTINGS["smart_log"]["enabled"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_iteration")?></td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[smart_log][iteration]" value="<?=$kit_SETTINGS["smart_log"]["iteration"]!=''?$kit_SETTINGS["smart_log"]["iteration"]:5?>"></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_save_props")?></td>
        <td width="60%"><input type="checkbox" size="40" name="SETTINGS[smart_log][settings][save_props]" value="Y"<?if($kit_SETTINGS["smart_log"]["settings"]["save_props"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_save_price")?></td>
        <td width="60%"><input type="checkbox" size="40" name="SETTINGS[smart_log][settings][save_price]" value="Y"<?if($kit_SETTINGS["smart_log"]["settings"]["save_price"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_save_count")?></td>
        <td width="60%"><input type="checkbox" size="40" name="SETTINGS[smart_log][settings][save_count]" value="Y"<?if($kit_SETTINGS["smart_log"]["settings"]["save_count"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_save_descr")?></td>
        <td width="60%"><input type="checkbox" size="40" name="SETTINGS[smart_log][settings][save_descr]" value="Y"<?if($kit_SETTINGS["smart_log"]["settings"]["save_descr"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_save_prev_descr")?></td>
        <td width="60%"><input type="checkbox" size="40" name="SETTINGS[smart_log][settings][save_prev_descr]" value="Y"<?if($kit_SETTINGS["smart_log"]["settings"]["save_prev_descr"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_save_img")?></td>
        <td width="60%"><input type="checkbox" size="40" name="SETTINGS[smart_log][settings][save_img]" value="Y"<?if($kit_SETTINGS["smart_log"]["settings"]["save_img"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_save_prev_img")?></td>
        <td width="60%"><input type="checkbox" size="40" name="SETTINGS[smart_log][settings][save_prev_img]" value="Y"<?if($kit_SETTINGS["smart_log"]["settings"]["save_prev_img"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_save_addit_img")?></td>
        <td width="60%"><input type="checkbox" size="40" name="SETTINGS[smart_log][settings][save_addit_img]" value="Y"<?if($kit_SETTINGS["smart_log"]["settings"]["save_addit_img"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_save_set_catalog")?></td>
        <td width="60%"><input type="checkbox" size="40" name="SETTINGS[smart_log][settings][save_set_catalog]" value="Y"<?if($kit_SETTINGS["smart_log"]["settings"]["save_set_catalog"] == "Y") echo " checked"?>></td>
    </tr>
    <?
    //Сервисы
    $tabControl->BeginNextTab();
    ?>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_loc_type_head")?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_loc_type")?>:</td>
        <td width="60%">
            <?=SelectBoxFromArray('SETTINGS[loc][type]', $arLocType, $kit_SETTINGS["loc"]["type"], "", "class='select_load'");?>
        </td>
    </tr>
    <?if(isset($kit_SETTINGS["loc"]["type"]) && $kit_SETTINGS["loc"]["type"]=="yandex"):?>
    <tr>
        <td width="40%"><?echo GetMessage("parser_loc_yandex_key")?>:</td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[loc][yandex][key]" value="<?=$kit_SETTINGS["loc"]["yandex"]["key"]?>"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_loc_yandex_key_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_loc_yandex_lang")?>:</td>
        <td width="60%"><input type="text" size="20" name="SETTINGS[loc][yandex][lang]" value="<?=$kit_SETTINGS["loc"]["yandex"]["lang"]?>"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_loc_yandex_lang_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_loc_fields")?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_loc_fields_name")?>:</td>
        <td width="60%"><input type="checkbox" name="SETTINGS[loc][f_name]" value="Y"<?if($kit_SETTINGS["loc"]["f_name"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_loc_fields_preview_text")?>:</td>
        <td width="60%"><input type="checkbox" name="SETTINGS[loc][f_preview_text]" value="Y"<?if($kit_SETTINGS["loc"]["f_preview_text"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_loc_fields_detail_text")?>:</td>
        <td width="60%"><input type="checkbox" name="SETTINGS[loc][f_detail_text]" value="Y"<?if($kit_SETTINGS["loc"]["f_detail_text"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_loc_fields_props")?>:</td>
        <td width="60%"><input type="checkbox" name="SETTINGS[loc][f_props]" value="Y"<?if($kit_SETTINGS["loc"]["f_props"] == "Y") echo " checked"?>></td>
    </tr>
    <?endif;?>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_loc_uniq")?></td>
    </tr>
    <?
    $engine = new Engine\Yandex();
    $arSettings = $engine->getSettings();
    $arDomains = \CSeoUtils::getDomainsList();
    
    foreach($arDomains as $key => $domain)
    {
        if(!isset($arSettings['SITES'][$domain['DOMAIN']]))
        {
            unset($arDomains[$key]);
        }
    }

    if(count($arDomains) <= 0)
    {
        $msg = new CAdminMessage(array(
            'MESSAGE' => Loc::getMessage('KIT_PARSER_SEO_YANDEX_ERROR'),
            'HTML' => 'Y'
        ));
    }else{
        $arrDomain['REFERENCE'][] =  Loc::getMessage('kit_parser_loc_uniq_no');
        $arrDomain['REFERENCE_ID'][] = "";
        foreach($arDomains as $domain)
        {   //printr($domain);
            $domainEnc = Converter::getHtmlConverter()->encode($domain['DOMAIN']);
            $arrDomain['REFERENCE'][] =  $domainEnc;
            $arrDomain['REFERENCE_ID'][] = $domainEnc;
        }
    }
    ?>
    <?if(count($arDomains) <= 0):?>
    <tr>
        <td colspan="2" align="center"><?echo $msg->Show();?></td>
    </tr>
    <?else:?>
    <tr>
        <td><?echo GetMessage("parser_loc_uniq_domain")?>:</td>
        <td><?=SelectBoxFromArray('SETTINGS[loc][uniq][domain]', $arrDomain, $kit_SETTINGS["loc"]["uniq"]["domain"], "", "");?></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_loc_uniq_domain_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?endif?>
    
    <?
    $tabControl->BeginNextTab();
    ?>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_video_2_0")?></td>
    </tr>
    <tr>
        <td align="center" colspan="2" width="100%">
            <?echo GetMessage("parser_video_2_0_text")?>
            <iframe width="800" height="500" src="https://www.youtube.com/embed/ej9bN2FgFls?list=PL2fR59TvIPXfA95wYKrG69YiG-nqFou9r" frameborder="0" allowfullscreen></iframe>
            <?echo GetMessage("parser_video_2_0_text1")?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_video_catalog_descr")?></td>
    </tr>
    <tr>
        <td align="center" colspan="2" width="100%">
        <?echo GetMessage("parser_video_2_0_text0")?>
        <iframe width="800" height="500" src="//www.youtube.com/embed/vIMmjeo-xSg?list=PL2fR59TvIPXfB_XDmyp7pCnYoqQ-HhPXl" frameborder="0" allowfullscreen>
        </iframe></td>
    </tr>
<?
if(isset($_GET["log_ID"]) && isset($_GET["ID"])):
    if (ob_get_level()) {
      ob_end_clean();
    }
    $file = $file_log;
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . basename($file));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit();
endif;
//********************
//Email-оповещение
//********************
$tabControl->BeginNextTab();  ?>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_notification")?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_notification_start")?>:</td>
        <td width="60%"><input type="checkbox" name="SETTINGS[notification][start]" value="Y"<?if($kit_SETTINGS["notification"]["start"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_notification_end")?>:</td>
        <td width="60%"><input type="checkbox" name="SETTINGS[notification][end]" value="Y"<?if($kit_SETTINGS["notification"]["end"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_notification_email")?>:</td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[notification][email]" value="<?=!empty($kit_SETTINGS["notification"]["email"])?$kit_SETTINGS["notification"]["email"]:COption::GetOptionString("main", "email_from")?>"></td>
    </tr>
<?php
$tabControl->Buttons(
    array(
        "disabled"=>($POST_RIGHT<"W"),
        "back_url"=>"list_parser_admin.php?lang=".LANG,

    )
);
?>
<?echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?=LANG?>">
<?if($ID>0 && !$bCopy):?>
    <input type="hidden" name="ID" value="<?=htmlspecialcharsbx($ID)?>">
<?endif;?>
<input type="hidden" name="parent" value="<?=htmlspecialcharsbx($parentID)?>">
<?
$tabControl->End();
?>

<?
$tabControl->ShowWarnings("post_form", $message);
?>