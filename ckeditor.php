<?php
/**
 *
 * Plugin	CKEditor
 * @author	Stephane F
 *
 **/

class ckeditor extends plxPlugin {

	public $valid_path = false;

	/**
	 * Constructeur de la classe ckeditor
	 *
	 * @param	default_lang	langue par défaut utilisée par PluXml
	 * @return	null
	 * @authors	Stephane F
	 **/
	public function __construct($default_lang) {

		# Appel du constructeur de la classe plxPlugin (obligatoire)
		parent::__construct($default_lang);

		# droits pour accéder à la page config.php du plugin
		$this->setConfigProfil(PROFIL_ADMIN);

		$this->addHook('AdminTopBottom', 'AdminTopBottom');

		$this->valid_path = is_dir(PLX_ROOT.($this->getParam("folder")));
		if($this->valid_path) {

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

			if(!isset($_POST['new_category'])) {
				# conversion des liens abs/rel dans les articles et les pages statiques
				$this->addHook('plxAdminEditArticle', 'Abs2Rel');
				$this->addHook('plxAdminEditStatique', 'Abs2Rel');
				# conversion des liens rel/abs dans les articles et les pages statiques
				$this->addHook('AdminArticleTop', 'Rel2Abs');
				$this->addHook('AdminStaticTop', 'Rel2Abs');
			}
		}
	}

	/**
	 * Méthode qui affiche un message si le répertoire de stockage des fichiers n'est pas définit dans la config du plugin
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function AdminTopBottom() {

		$string = '
		if($plxAdmin->plxPlugins->aPlugins["ckeditor"]->getParam("folder")=="") {
			echo "<p class=\"warning\">Plugin ckEditor<br />'.$this->getLang("L_ERR_FOLDER_NOT_DEFINED").'</p>";
			plxMsg::Display();
		} elseif(!$plxAdmin->plxPlugins->aPlugins["ckeditor"]->valid_path) {
			echo "<p class=\"warning\">Plugin ckEditor<br />'.$this->getLang("L_ERR_FOLDER_INVALID_DIR").'</p>";
			plxMsg::Display();
		}';
		echo '<?php '.$string.' ?>';

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

		echo '<script src="'.PLX_PLUGINS.'ckeditor/ckeditor/ckeditor.js"></script>'."\n";
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
		$height = trim($this->getParam('height'));
		if($height!='') $height = 'height:'.(is_numeric($height) ? $height : '"'.$height.'"').',';

?>
<script>
<!--
if(typeof CKEDITOR != 'undefined') {
	var roxyFileman = '<?php echo PLX_PLUGINS ?>ckeditor/fileman/index.html?integration=ckeditor';
	var textareas = document.getElementsByTagName("textarea");
	for(var i=0;i<textareas.length;i++) {
		var n = textareas[i].name;
		if(n=="content" || n=="chapo") {
			CKEDITOR.replace('id_'+n, {
				extraPlugins: 'justify,showblocks,widget,lineutils,oembed<?php echo $extraPlugins ?>',
				<?php echo $height ?>
				scayt_autoStartup: true,
				extraAllowedContent: 'video[*]{*}',
				filebrowserBrowseUrl: roxyFileman,
				filebrowserImageBrowseUrl: roxyFileman+'&type=image',
				removeDialogTabs: 'link:upload;image:upload',
				entities: false,
				allowedContent: true
			});
		}
	}
}
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
		if(isset($content["chapo"])) {
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

}
?>
