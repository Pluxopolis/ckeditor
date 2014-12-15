<?php
/**
 *
 * Plugin	CKEditor
 * @author	Stephane F
 *
 **/

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

		# répertoire racine d'installation de PluXml sur le serveur
		$dir = str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"]));
		$this->racine = trim(preg_replace("/\/(core|plugins)\/(.*)/", "", $dir), "/")."/";
		$this->racine = $this->racine[0]!="/" ? "/".$this->racine : $this->racine;

		# déclaration pour ajouter l'éditeur
		$static = $this->getParam('static')==1 ? '' : '|statique';
		if(!preg_match('/(parametres_edittpl|comment'.$static.')/', basename($_SERVER['SCRIPT_NAME']))) {
			$this->addHook('AdminTopEndHead', 'AdminTopEndHead');
			$this->addHook('AdminFootEndBody', 'AdminFootEndBody');
		}

		# si affichage des articles coté visiteurs: protection des emails contre le spam
		if(!defined('PLX_ADMIN')) {
			$this->addHook('plxMotorParseArticle', 'protectEmailsArticles');
			$this->addHook('plxShowStaticContent', 'protectEmailsStatics');
		}

		# conversion des liens abs/rel dans les articles et les pages statiques
		$this->addHook('plxAdminEditArticle', 'Abs2Rel');
		$this->addHook('plxAdminEditStatique', 'Abs2Rel');
		# conversion des liens rel/abs dans les articles et les pages statiques
		$this->addHook('AdminArticleTop', 'Rel2Abs');
		$this->addHook('AdminStaticTop', 'Rel2Abs');
	}


	/**
	 * Méthode qui ajoute la déclaration du script javascript de ckeditor dans la partie <head>
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function AdminTopEndHead() {

		$plxAdmin = plxAdmin::getinstance();
		$_SESSION['FILEMAN_FILES_ROOT'] = $this->racine.trim($this->getParam('folder'), "/");
		if($plxAdmin->aConf['userfolders'] AND $_SESSION['profil']==PROFIL_WRITER) {
			$_SESSION['FILEMAN_FILES_ROOT'] .= "/".$_SESSION['user'];
		}

		echo '<script type="text/javascript" src="'.PLX_PLUGINS.'ckeditor/ckeditor/ckeditor.js"></script>'."\n";
	}

	/**
	 * Méthode qui ajoute la déclaration du script javascript de ckeditor en bas de page
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function AdminFootEndBody() {

		$extra = trim($this->getParam('extraPlugins'));
		$extraPlugins = ($extra !='' ? ','.$extra : '');
		$extraPlugins = str_replace(' ', '', $extraPlugins);

?>
<script>
<!--
if(typeof CKEDITOR != 'undefined') {
	var roxyFileman = '<?php echo PLX_PLUGINS ?>ckeditor/fileman/index.html?integration=ckeditor';
	var textareas = document.getElementsByTagName("textarea");
	for(var i=0;i<textareas.length;i++) {
		CKEDITOR.replace('id_'+textareas[i].name, {
			extraPlugins: 'justify,showblocks,widget,lineutils,oembed<?php echo $extraPlugins ?>',
			scayt_autoStartup: true,
			extraAllowedContent: 'video[*]{*}',
			filebrowserBrowseUrl: roxyFileman,
			filebrowserImageBrowseUrl: roxyFileman+'&type=image',
			removeDialogTabs: 'link:upload;image:upload'
		});
	}
}
<?php 
		$static = $this->getParam('static')==1 ? '' : '|statique';

if(preg_match('/(article'.$static.')/', basename($_SERVER['SCRIPT_NAME']))) : ?>
window.onbeforeunload = function(e) {
  return ' ';
};
<?php endif; ?>
-->
</script>
<?php
//extraAllowedContent: 'video[*]{*};source[*]{*}',
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
		$abs_path_images = "'.$this->racine.$this->getParam('folder').'";
		$rel_path_images = "'.$this->getParam('folder').'";

		# Les liens absolus commençant par http://www.domaine.com/ sont convertis en liens relatifs
		if(isset($chapo)) {
			$content["chapo"] = str_replace($abs_path_images, $rel_path_images, $content["chapo"]);
		}
		$content["content"] = str_replace($abs_path_images, $rel_path_images, $content["content"]);

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

		if(!isset($_POST["draft"]) AND !isset($_POST["publish"]) AND !isset($_POST["update"]) AND !isset($_POST["moderate"])) {
			# Préparation des variables
			$abs_path_images = "'.$this->racine.$this->getParam('folder').'";
			$rel_path_images = "'.$this->getParam('folder').'";

			# Les liens relatifs sont convertis en liens absolus, pour que les images soient visibles dans CKEditor
			if(isset($chapo)) {
				$chapo = str_replace($rel_path_images, $abs_path_images, $chapo);
			}
			$content = str_replace($rel_path_images, $abs_path_images, $content);
		}

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

		if(preg_match_all('/<a.+href=[\'"]mailto:([\._a-zA-Z0-9-@]+)((\?.*)?)[\'"][^>]*>([\._a-zA-Z0-9-@]+)<\/a>/i', $txt, $matches)) {		
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

}
?>
