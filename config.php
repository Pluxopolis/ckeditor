<?php if(!defined('PLX_ROOT')) exit; ?>
<?php

# Control du token du formulaire
plxToken::validateFormToken($_POST);

if(!empty($_POST)) {

	$plxPlugin->setParam('folder', trim(ltrim($_POST['folder'], '/')), 'string');
	$plxPlugin->setParam('static', $_POST['static'], 'numeric');
	$plxPlugin->setParam('extraPlugins', $_POST['extraPlugins'], 'cdata');
	$plxPlugin->setParam('height', $_POST['height'], 'string');
	$plxPlugin->saveParams();
	header('Location: parametres_plugin.php?p=ckeditor');
	exit;
}
?>

<h2><?php echo $plxPlugin->getInfo('title') ?></h2>
<?php
?>
<form id="form_ckeditor" action="parametres_plugin.php?p=ckeditor" method="post">
	<fieldset>
		<p class="field"><label for="id_folder"><?php echo $plxPlugin->lang('L_FOLDER') ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('folder',$plxPlugin->getParam('folder'),'text','40-255') ?>
		<a class="help" title="<?php echo L_HELP_SLASH_END ?>">&nbsp;</a>&nbsp;ex: data/images/
		<p class="field"><label for="id_static"><?php echo $plxPlugin->lang('L_STATIC') ?></label></p>
		<?php plxUtils::printSelect('static',array('1'=>L_YES,'0'=>L_NO), $plxPlugin->getParam('static'));?>
		<p class="field"><label for="id_height"><?php echo $plxPlugin->lang('L_EDITOR_HEIGHT') ?></label></p>
		<?php plxUtils::printInput('height',$plxPlugin->getParam('height'),'text','7-7') ?>
		<p class="field"><label for="id_extraPlugins">extraPlugins&nbsp;:</label></p>
		<?php plxUtils::printInput('extraPlugins',$plxPlugin->getParam('extraPlugins'),'text','40-255') ?>
		<a class="help" title="<?php $plxPlugin->lang('L_COMMA') ?>">&nbsp;</a>&nbsp;
		<a href="http://ckeditor.com/addons/plugins/all" title="extraPlugins">http://ckeditor.com/addons/plugins/all</a>
		<p>
			<?php echo plxToken::getTokenPostMethod() ?>
			<input type="submit" name="submit" value="<?php $plxPlugin->lang('L_SAVE') ?>" />
		</p>
	</fieldset>
</form>
