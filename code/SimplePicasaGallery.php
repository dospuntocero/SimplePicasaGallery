<?php

class SimplePicasaGallery extends Page {
	static $singular_name = "Simple Picasa Gallery";
	static $icon = "SimplePicasaGallery/images/picasa";
	
	static $db = array(
		'Username' => 'Varchar(255)',
		'ThumbSize' => 'Enum("32, 48, 64, 72, 104, 144, 150, 160","104")',
		'AlbumsThumbSize' => 'Enum("32, 48, 64, 72, 104, 144, 150, 160","104")',
		'ThumbShape' => 'Varchar',
		'ImageSize' => 'Enum("94, 110, 128, 200, 220, 288, 320, 400, 512, 576, 640, 720, 800, 912, 1024, 1152, 1280, 1440, 1600","800")',
		'ImageShape' => 'Varchar',
		'ShowDescription' => 'Boolean',
	);

	function getCMSFields() {
		$fields = parent::getCMSFields();


		$fields->removeFieldFromTab("Root.Content","Layout");
		$fields->removeFieldFromTab("Root.Content","Estructura");		
		$ShapeProvider = array(
			'c' => 'Cropped',
			'u' => 'Uncropped'
		);
				

		$AlbumsThumbSize = new DropdownField('AlbumsThumbSize',_t('SimplePicasaGallery.ALBUMSTHUMBSIZE',"Albums Thumbnail Size"),$this->DBObject('AlbumsThumbSize')->enumValues());
		$AlbumsThumbSize->setEmptyString(_t('SimplePicasaGallery.THUMBSIZE',"Albums Thumbnail Size"));


		$ThumbSize = new DropdownField('ThumbSize',_t('SimplePicasaGallery.THUMBSIZE',"Thumbnail Size"),$this->DBObject('ThumbSize')->enumValues());
		$ThumbSize->setEmptyString(_t('SimplePicasaGallery.THUMBSIZE',"Thumbnail Size"));

		$ThumbShape = new DropdownField('ThumbShape',_t('SimplePicasaGallery.THUMBSHAPE',"Thumbnail Shape"),$ShapeProvider);
		$ThumbShape->setEmptyString("Thumbnail Shape");

		$ImageSize = new DropdownField('ImageSize',_t('SimplePicasaGallery.IMAGESIZE',"Image Size"),$this->DBObject('ImageSize')->enumValues());
		$ImageSize->setEmptyString("Image Size");


		$ImageShape = new DropdownField('ImageShape',_t('SimplePicasaGallery.IMAGESHAPE',"Image Shape"),$ShapeProvider);
		$ImageShape->setEmptyString("Image Shape");		


		$fields->removeFieldFromTab("Root.Content.Main","Content");
		$fields->addFieldsToTab("Root.Content.Main",array(

			new TextField('Username',_t('SimplePicasaGallery.USERNAME',"Username")),
			new CheckboxField('ShowDescription',_t('SimplePicasaGallery.SHOWDESCRIPTION',"Show description for pictures?")),
			$AlbumsThumbSize,
			$ThumbSize,
			$ThumbShape,
			$ImageSize,
			$ImageShape
		));
		return $fields;
	}


}
class SimplePicasaGallery_Controller extends Page_Controller {
	
	
	function init(){
		parent::init();
	}
	
	function PicasaAlbumList(){
		$file = file_get_contents("http://picasaweb.google.com/data/feed/api/user/".$this->Username."?kind=album&access=public&thumbsize=".$this->AlbumsThumbSize.$this->ThumbShape);

		$xml = new SimpleXMLElement($file);
		$xml->registerXPathNamespace('gphoto', 'http://schemas.google.com/photos/2007');
		$xml->registerXPathNamespace('media', 'http://search.yahoo.com/mrss/');
		$AlbumsList = new DataObjectSet();

		foreach($xml->entry as $feed){
			$Picture = new DataObject();
			$Group = $feed->xpath('./media:group/media:thumbnail');
			$attributes = $Group[0]->attributes(); // we need thumbnail path
			$id = $feed->xpath('./gphoto:id'); // and album id for our thumbnail
			$Picture->Thumb = $attributes['url'];
			$Picture->Id = $id[0][0];
			$AlbumsList->push($Picture);
		}
		return $AlbumsList;
	}
	
	function viewalbum(){
		$Albumid = $this->urlParams['ID']; // let's put album id here so it is easie to use later 
		$file = file_get_contents('http://picasaweb.google.com/data/feed/api/user/'.$this->Username.'/albumid/'.$Albumid.'?kind=photo&access=public&thumbsize='.$this->ThumbSize.$this->ThumbShape.'&imgmax=720u');
		$xml = new SimpleXMLElement($file); // convert feed into simplexml object
		$xml->registerXPathNamespace('media', 'http://search.yahoo.com/mrss/'); // define namespace media
		$Album = new DataObjectSet();
		
		foreach($xml->entry as $feed){ // go over the pictures
			$Picture = new DataObject();
			$group = $feed->xpath('./media:group/media:thumbnail'); // let's find thumbnail tag
			$description = $feed->xpath('./media:group/media:description');
			
				if(str_word_count($description[0]) > 0){ // if picture has description, we'll use it as title
					$description = $description[0]; // file name appended by image captioning
				}else{
					$description =$feed->title; // if not will use file name as title
				}
				
			$attributes = $group[0]->attributes(); // now we need to get attributes of thumbnail tag, so we can extract the thumb link
			$content = $feed->content->attributes(); // now we convert "content" attributes into array
			$Picture->Thumb = $attributes['url'];
			$Picture->Image = $content['src'];
			$Picture->Description = $description;
			$Album->push($Picture);
		}
		return $this->customise(array(
			"AlbumPictures" => $Album
		));
		
	}
	
	function PicasaNickName(){
		$file = file_get_contents("http://picasaweb.google.com/data/feed/api/user/".$this->Username."?kind=album&access=public&thumbsize=".$this->ThumbSize.$this->ThumbShape);

		$xml = new SimpleXMLElement($file);
		$xml->registerXPathNamespace('gphoto', 'http://schemas.google.com/photos/2007');
		$xml->registerXPathNamespace('media', 'http://search.yahoo.com/mrss/');
		$nickname = $xml->xpath('//gphoto:nickname');
		$NickName = $nickname[0];
		return $NickName;
	}
}