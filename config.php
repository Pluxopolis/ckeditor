<?php if(!defined('PLX_ROOT')) exit; ?>
<?php

# Control du token du formulaire
plxToken::validateFormToken($_POST);

if(!empty($_POST)) {

	$dir = realpath(PLX_ROOT.$_POST['uplDir']);
	if(!is_dir($dir)) {
		plxMsg::Error($plxPlugin->getLang('L_ERROR_INVALID_DIR'));
	} else {
		$plxPlugin->setParam('uplDir', ltrim($_POST['uplDir'],'/'), 'cdata');
		$plxPlugin->setParam('skin', $_POST['skin'], 'cdata');
		$plxPlugin->setParam('oembed', $_POST['oembed'], 'numeric');
		$plxPlugin->setParam('syntaxhighlight', $_POST['syntaxhighlight'], 'numeric');
		$plxPlugin->setParam('syntaxhighlight_style', $_POST['syntaxhighlight_style'], 'cdata');
		$plxPlugin->setParam('lightbox', $_POST['lightbox'], 'numeric');
		$plxPlugin->setParam('static', $_POST['static'], 'numeric');
		$plxPlugin->saveParams();
	}
	header('Location: parametres_plugin.php?p=ckeditor');
	exit;
}

# On récupère les thèmes disponibles pour la coloration syntaxiques
$aStyles = array();
$files = plxGlob::getInstance(PLX_PLUGINS.'ckeditor/syntaxhighlighter/styles/');
if($styles = $files->query("/shTheme(.*).css/i")) {
	foreach($styles as $k=>$v) {
		$aStyles[$v] = $v;
	}
}
$default_style = $plxPlugin->getParam('syntaxhighlight_style')!='' ? $plxPlugin->getParam('syntaxhighlight_style') : 'shThemeDefault.css';

?>

<h2><?php echo $plxPlugin->getInfo('title') ?></h2>
<?php
?>
<form id="form_ckeditor" action="parametres_plugin.php?p=ckeditor" method="post">
	<fieldset>
		<p class="field"><label for="id_uplDir"><?php echo $plxPlugin->lang('L_UPLOAD_DIR') ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('uplDir',$plxPlugin->getParam('uplDir'),'text','40-255') ?>
		<a class="help" title="<?php echo L_HELP_SLASH_END ?>">&nbsp;</a><strong>ex: data/</strong>
		<p class="field"><label for="id_skin"><?php echo $plxPlugin->lang('L_SKIN') ?>&nbsp;:</label></p>
		<?php plxUtils::printSelect('skin',array('BootstrapCK-Skin'=>'BootstrapCK-Skin','kama'=>'kama'),$plxPlugin->getParam('skin')); ?>
		<p class="field"><label for="id_static"><?php echo $plxPlugin->lang('L_STATIC') ?></label></p>
		<?php plxUtils::printSelect('static',array('1'=>L_YES,'0'=>L_NO), $plxPlugin->getParam('static'));?>
		<br /><br /><br />
		<p><?php echo $plxPlugin->lang('L_PLUGINS_LEGEND') ?>&nbsp;:</p>
		<p class="field"><label for="id_oembed"><?php echo $plxPlugin->lang('L_OEMBED') ?></label></p>
		<?php plxUtils::printSelect('oembed',array('1'=>L_YES,'0'=>L_NO), $plxPlugin->getParam('oembed'));?>
		&nbsp;<img src="<?php echo PLX_PLUGINS ?>ckeditor/ckeditor/plugins/oEmbed/images/icon.png" alt="oEmbed" />
		<p class="field"><label for="id_syntaxhighlight"><?php echo $plxPlugin->lang('L_SYNTAX_HIGHLIGHT') ?></label></p>
		<?php plxUtils::printSelect('syntaxhighlight',array('1'=>L_YES,'0'=>L_NO), $plxPlugin->getParam('syntaxhighlight'));?>
		&nbsp;<img src="<?php echo PLX_PLUGINS ?>ckeditor/ckeditor/plugins/syntaxhighlight/images/syntaxhighlight.gif" alt="Syntax Highlight" />
		<p class="field"><label for="id_syntaxhighlight_style">&nbsp;</label></p>
		<?php plxUtils::printSelect('syntaxhighlight_style', $aStyles, $default_style) ?>
		&nbsp;<?php echo $plxPlugin->lang('L_SYNTAX_HIGHLIGHT_STYLE') ?>
		<p class="field"><label for="id_lightbox"><?php echo $plxPlugin->lang('L_LIGHTBOX_PLUS') ?></label></p>
		<?php plxUtils::printSelect('lightbox',array('1'=>L_YES,'0'=>L_NO), $plxPlugin->getParam('lightbox'));?>
		<p>
			<?php echo plxToken::getTokenPostMethod() ?>
			<input type="submit" name="submit" value="<?php $plxPlugin->lang('L_SAVE') ?>" />
		</p>
	</fieldset>
</form>
