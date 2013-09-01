<?php
/**
 *
 * Plugin	CKEditor
 * @author	Stephane F
 *
 **/

function getRacine() {
	$plxAdmin = plxAdmin::getInstance();
	return $plxAdmin->aConf['racine'];
}

class ckeditor extends plxPlugin {

	/**
	 * Constructeur de la classe ckeditor
	 *
	 * @param	default_lang	langue par défaut utilisée par PluXml
	 * @return	null
	 * @authors	Stephane F - Francis
	 **/
	public function __construct($default_lang) {

		# Appel du constructeur de la classe plxPlugin (obligatoire)
		parent::__construct($default_lang);

		# droits pour accéder à la page config.php du plugin
		$this->setConfigProfil(PROFIL_ADMIN);

		$this->addHook('AdminTopBottom', 'AdminTopBottom');

		# si affichage des articles coté visiteurs: protection des emails contre le spam
		if(!defined('PLX_ADMIN')) {
			$this->addHook('plxMotorParseArticle', 'protectEmailsArticles');
			$this->addHook('plxShowStaticContent', 'protectEmailsStatics');
			$this->addHook('ThemeEndHead', 'ThemeEndHead');
			$this->addHook('ThemeEndBody', 'ThemeEndBody');
			$this->addHook('IndexEnd', 'IndexEnd');
		} else {
			# affichage coté administration si on est pas sur les pages parametres_edittpl.php, comment.php et page statiques (cf config plugin)
			$static = $this->getParam('static')==1 ? '' : '|statique';
			if(!preg_match('/(parametres_edittpl|comment'.$static.')/', basename($_SERVER['SCRIPT_NAME']))) {

				$protocol = (!empty($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] == 'on')?	'https://' : "http://";
				$servername = $_SERVER['HTTP_HOST'];
				$serverport = (preg_match('/:[0-9]+/', $servername) OR $_SERVER['SERVER_PORT'])=='80' ? '' : ':'.$_SERVER['SERVER_PORT'];
				preg_match("/(.*)\/core\/admin/i", $_SERVER['SCRIPT_NAME'], $capture);
				$_SESSION['ckeditor_url'] = $protocol.$servername.$serverport.$capture[1].'/'.$this->getParam('uplDir');

				# déclaration pour ajouter l'éditeur
				$this->addHook('AdminTopEndHead', 'AdminTopEndHead');
				$this->addHook('AdminFootEndBody', 'AdminFootEndBody');
				# pour les articles
				$this->addHook('plxAdminEditArticle', 'Abs2Rel');
				$this->addHook('AdminArticleTop', 'Rel2Abs');
				# pour les pages statiques
				$this->addHook('plxAdminEditStatique', 'Abs2Rel');
				$this->addHook('AdminStaticTop', 'Rel2Abs');

			}
		}

	}

	/**
	 * Méthode qui convertit les liens absolus en liens relatifs
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function onActivate() {
		if(!is_file($this->plug['parameters.xml'])) {
			$this->setParam('uplDir', 'data/', 'cdata');
			$this->setParam('skin', 'BootstrapCK-Skin', 'cdata');
			$this->setParam('oembed', 1, 'numeric');
			$this->setParam('static', 0, 'numeric');
			$this->setParam('syntaxhighlight', 0, 'numeric');
			$this->setParam('syntaxhighlight_style', 'shThemeDefault.css', 'cdata');
			$this->saveParams();
		}
	}

	/**
	 * Méthode qui convertit les liens absolus en liens relatifs
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function Abs2Rel() {

		echo '<?php

		# Préparation des variables
		$abs_path_images = $this->racine.$this->aConf["images"];
		$rel_path_images = $this->aConf["images"];

		$abs_path_docs = $this->racine.$this->aConf["documents"];
		$rel_path_docs = $this->aConf["documents"];

		# Les liens absolus commençant par http://www.domaine.com/ sont convertis en liens relatifs
		if(isset($content["chapo"])) {
			$content["chapo"] = str_replace($abs_path_images, $rel_path_images, $content["chapo"]);
			$content["chapo"] = str_replace($abs_path_docs, $rel_path_docs,$content["chapo"]);
		}
		$content["content"] = str_replace($abs_path_images, $rel_path_images, $content["content"]);
		$content["content"] = str_replace($abs_path_docs, $rel_path_docs, $content["content"]);

		?>';

	}

	/**
	 * Méthode qui convertit les liens relatifs en liens absolus
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function Rel2Abs() {

		echo '<?php

		# Préparation des variables
		$abs_path_images = $plxAdmin->racine.$plxAdmin->aConf["images"];
		$rel_path_images = $plxAdmin->aConf["images"];

		$abs_path_docs = $plxAdmin->racine.$plxAdmin->aConf["documents"];
		$rel_path_docs = $plxAdmin->aConf["documents"];

		# Les liens relatifs sont convertis en liens absolus, pour que les images soient visibles dans CKEditor
		if(isset($chapo)) {
			$chapo = str_replace($rel_path_images, $abs_path_images, $chapo);
			$chapo = str_replace($rel_path_docs, $abs_path_docs, $chapo);
		}
		$content = str_replace($rel_path_images, $abs_path_images, $content);
		$content = str_replace($rel_path_docs, $abs_path_docs, $content);

		?>';
	}

	/**
	 * Méthode qui encode une chaine de caractère en hexadecimal
	 *
	 * @parm	s		chaine de caractères à encoder
	 * @return	string	chane de caractères encodée en hexadecimal
	 * @author	Stephane F
	 **/
	public static function encodeBin2Hex($s) {

		$encode = '';
		for ($i = 0; $i < strlen($s); $i++) {
			$encode .= '%' . bin2hex($s[$i]);
		}
		return $encode;
	}

	/**
	 * Méthode qui protège les adresses emails contre le spam
	 *
	 * @parm	txt		chaine de caractères à protéger
	 * @return	string	chaine de caractères avec les adresses emails protégées
	 * @author	Stephane F, Francis
	 **/
	public static function protectEmails($txt) {

		if(preg_match_all('/<a.+href=[\'"]mailto:([\._a-zA-Z0-9-@]+)((\?.*)?)[\'"]>([\._a-zA-Z0-9-@]+)<\/a>/i', $txt, $matches)) {
			foreach($matches[0] as $k => $v) {
				$string = ckeditor::encodeBin2Hex('document.write(\''.$matches[0][$k].'\')');
				$txt = str_replace($matches[0][$k], '<script type="text/javascript">eval(unescape(\''.$string.'\'))</script>' , $txt);
			}
		}
		$s = preg_replace('/<input(\s+[^>]*)?>/i', '', $txt);
		$s = preg_replace('/<textarea(\s+[^>]*)?>.*?<\/textarea(\s+[^>]*)?>/i', '', $s);
		if(preg_match_all('/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i', $s, $matches)) {
			foreach($matches[0] as $k => $v) {
				$string = ckeditor::encodeBin2Hex('document.write(\''.$matches[0][$k].'\')');
				$txt = str_replace($matches[0][$k], '<script type="text/javascript">eval(unescape(\''.$string.'\'))</script>' , $txt);
			}
		}
		return $txt;
	}

	/**
	 * Méthode pour prendre en compte le mode transparent des iframes
	 *
	 * @parm	html	chaine de caractères à scanner
	 * @return	string	chaine de caractères modifiée
	 * @author	Stephane F
	 **/
	public static function wmodeTransparent($html) {

		if(strpos($html, "<embed src=" ) !== false) {
			return str_replace('</param><embed', '</param><param name="wmode" value="transparent"></param><embed wmode="transparent" ', $html);
		} elseif(strpos($html, 'feature=oembed') !== false) {
			return str_replace('feature=oembed', 'feature=oembed&amp;wmode=transparent', $html);
		} else {
			return $html;
		}
	}

	public function IndexEnd() {
		echo '<?php $output = ckeditor::wmodeTransparent($output) ?>';
	}

	/**
	 * Méthode qui protège les adresses emails contre le spam dans les articles
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function protectEmailsArticles() {

		echo '<?php
			$art["chapo"] = ckeditor::protectEmails($art["chapo"]);
			$art["content"] = ckeditor::protectEmails($art["content"]);
		?>';

	}

	/**
	 * Méthode qui protège les adresses emails contre le spam dans les pages statiques
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function protectEmailsStatics() {

		echo '<?php
			$output = ckeditor::protectEmails($output);
		?>';

	}

	/**
	 * Méthode qui affiche un message si le répertoire d'upload n'est pas définit dans la config du plugin
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function AdminTopBottom() {

		$string = '
		if($plxAdmin->plxPlugins->aPlugins["ckeditor"]->getParam("uplDir")=="") {
			echo "<p class=\"warning\">Plugin ckEditor<br />'.$this->getLang("L_ERR_UPLDIR_NOT_DEFINED").'</p>";
			plxMsg::Display();
		}';
		echo '<?php '.$string.' ?>';

	}

	/**
	 * Méthode qui ajoute la déclaration du script javascript de ckeditor
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function AdminTopEndHead() {

		echo '<script type="text/javascript" src="'.PLX_PLUGINS.'ckeditor/ckeditor/ckeditor.js"></script>'."\n";
	}

	/**
	 * Méthode qui ajoute les paramètres d'initialisation pour ckeditor
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function AdminFootEndBody() {

		$extraPlugins = array();
		$buttons = array();
		if($this->getParam('oembed')) {
			$extraPlugins[] = 'oEmbed';
			$buttons['oembed'] = "'oEmbed',";
		}
		if($this->getParam('syntaxhighlight')) {
			$extraPlugins[] = 'syntaxhighlight';
			$buttons['syntaxhighlight'] = "'Code',";
		}
		if($this->getParam('lightbox')) {
			$extraPlugins[] = 'lightbox';
		}

	?>
	<script type="text/javascript">
	<!--
	if(typeof CKEDITOR != 'undefined') {
		var textareas = document.getElementsByTagName("textarea");
		for(var i=0;i<textareas.length;i++) {
			CKEDITOR.replace('id_'+textareas[i].name, {
				skin: '<?php echo $this->getParam('skin')==''?'BootstrapCK-Skin':$this->getParam('skin') ?>',
				extraPlugins: '<?php echo implode(',', $extraPlugins) ?>',
				width: '97%',
				language: '<?php echo $this->default_lang ?>',
				<?php
				if($this->getParam('skin')=='kama') {
				echo "
				toolbar :
				[
					['Source','-','Undo','Redo','Cut','Copy','Paste','PasteText','PasteFromWord','-','Find','Replace','-','SelectAll','RemoveFormat'],
					['-','Table','HorizontalRule','Smiley','SpecialChar'],
					'/',
					['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
					['Bold','Italic','Underline','Strike','-','Link','Image','Flash',".plxUtils::getValue($buttons['oembed']).plxUtils::getValue($buttons['syntaxhighlight'])."'Unlink','Anchor','-','NumberedList','BulletedList','-','Outdent','Indent','-','Subscript','Superscript','Blockquote'],
					'/',
					['Format','Font','FontSize', 'TextColor','BGColor','-','Maximize', 'ShowBlocks']
				],
				";
				} else {
				echo "
				toolbar :
				[
					['Format','Font','FontSize','Image','Flash',".plxUtils::getValue($buttons['oembed']).plxUtils::getValue($buttons['syntaxhighlight'])."'-','Find','Replace','-','SelectAll','RemoveFormat','-','Table','HorizontalRule','Smiley','SpecialChar','-','ShowBlocks','Maximize','-','Source'],
					'/',
					['Undo','Redo','Cut','Copy','Paste','PasteText','PasteFromWord','-','Bold','Italic','Underline','Strike','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','TextColor','BGColor','-','Link','Unlink','Anchor','-','NumberedList','BulletedList','Outdent','Indent','Subscript','Superscript','Blockquote'],
				],
				";
				}
				?>
				// Link dialog, "Browse Server" button
				filebrowserBrowseUrl : '<?php echo PLX_PLUGINS ?>ckeditor/kcfinder/browse.php?type=documents',
				// Image dialog, "Browse Server" button
				filebrowserImageBrowseUrl : '<?php echo PLX_PLUGINS ?>ckeditor/kcfinder/browse.php?type=images',
				// Flash dialog, "Browse Server" button
				filebrowserFlashBrowseUrl : '<?php echo PLX_PLUGINS ?>ckeditor/kcfinder/browse.php?type=documents',
				// Upload tab in the Link dialog
				filebrowserUploadUrl : '<?php echo PLX_PLUGINS ?>ckeditor/kcfinder/upload.php?type=documents',
				// Upload tab in the Image dialog
				filebrowserImageUploadUrl : '<?php echo PLX_PLUGINS ?>ckeditor/kcfinder/upload.php?type=images',
				// Upload tab in the Flash dialog
				filebrowserFlashUploadUrl : '<?php echo PLX_PLUGINS ?>ckeditor/kcfinder/upload.php?type=documents',
				// Filebrowser width
				filebrowserWindowWidth : '1000',
				// Filebrowser height
				filebrowserWindowHeight : '700'
			});
		}
	}
	-->
	</script>

	<?php
	}

	/**
	 * Méthode qui ajoute les déclarations pour la coloration syntaxique
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function ThemeEndHead() {

		if($this->getParam('syntaxhighlight')) {
			echo '
	<script src="'.PLX_PLUGINS.'ckeditor/syntaxhighlighter/scripts/shCore.js" type="text/javascript"></script>
	<script src="'.PLX_PLUGINS.'ckeditor/syntaxhighlighter/scripts/shAutoloader.js" type="text/javascript"></script>
	<link href="'.PLX_PLUGINS.'ckeditor/syntaxhighlighter/styles/shCore.css" rel="stylesheet" type="text/css" />
	<link href="'.PLX_PLUGINS.'ckeditor/syntaxhighlighter/styles/'.$this->getParam('syntaxhighlight_style').'" rel="stylesheet" type="text/css" />
	<style type="text/css">
	.syntaxhighlighter { font-size:12px !important; padding-top:10px; padding-bottom:10px; }
	</style>
			';
		}
		if($this->getParam('lightbox')) {
			echo '
	<script src="'.PLX_PLUGINS.'ckeditor/lightbox/lightbox_plus-min.js" type="text/javascript"></script>
			';
		}
	}

	/**
	 * Méthode qui ajoute les déclarations pour la coloration syntaxique
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function ThemeEndBody() {

		if($this->getParam('syntaxhighlight')) {
			$plxMotor = plxMotor::getInstance();
			echo '
<script type="text/javascript">
<!--
function path() {
	var args = arguments, result = [];
	for(var i = 0; i < args.length; i++)
		result.push(args[i].replace("@", "'.$plxMotor->urlRewrite(PLX_PLUGINS.'ckeditor/syntaxhighlighter/scripts/').'"));
	return result
};
SyntaxHighlighter.autoloader.apply(null, path(
	"bash shell             @shBrushBash.js",
	"c# c-sharp csharp      @shBrushCSharp.js",
	"cpp c                  @shBrushCpp.js",
	"css                    @shBrushCss.js",
	"delphi pascal          @shBrushDelphi.js",
	"diff patch pas         @shBrushDiff.js",
	"js jscript javascript  @shBrushJScript.js",
	"java                   @shBrushJava.js",
	"perl pl                @shBrushPerl.js",
	"php                    @shBrushPhp.js",
	"text plain             @shBrushPlain.js",
	"py python              @shBrushPython.js",
	"ruby rails ror rb      @shBrushRuby.js",
	"sql                    @shBrushSql.js",
	"vb vbnet               @shBrushVb.js",
	"xml xhtml xslt html    @shBrushXml.js"
));
SyntaxHighlighter.defaults[\'toolbar\'] = false;
SyntaxHighlighter.all();
-->
</script>'."\n";
		}
	}

}
?>
