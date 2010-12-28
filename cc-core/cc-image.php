<?php

class Image {
	private $img;
	private $type;
	private $useGD = false;
	private $useImagick = false;

	public static $gd = true;
	public static $imagick = false;

	/**
	 * Create a new instance of Image.
	 *
	 * @param mixed $file A an instance of GD_Image, an instance of Imagick or the path to an image.
	 */
	public function  __construct ($file) {
		if($file instanceof GD_Image) {
			$this->useGD = true;
			$this->img = $file;
		}
		else if(class_exists ('Imagick') && $file instanceof Imagick) {
			$this->useImagick = true;
			$this->img = $file;
		}
		else if(is_string($file)) {
			if(!file_exists($file)) {
				throw new Exception("$file doesn't exist.");
			}
			if(self::$imagick) {
				$this->img = new Imagick($file);
			}
			else if(self::$gd) {
				$this->img = new GD_Image($file);
			}
		}
	}

	public function resize ($x, $y) {
		if($this->useGD) {
			return $this->img->resize($x, $y);
		}
		else {
			return $this->img->scaleImage($x, $y);
		}
	}

	public function crop ($startx, $starty, $endx, $endy) {
		if($this->useGD) {
			return $this->img->crop($startx, $starty, $endx, $endy);
		}
		else {
			return $this->img->cropImage($endx - $startx, $endy - $starty, $startx, $starty);
		}
	}

	public function rotate ($degrees, $backgroundRGB = array(255,255,255,0.0)) {
		if($this->useGD) {
			return $this->img->rotate($degrees, $backgroundRGB);
		}
		else {
			return $this->img->rotateImage(new ImagickPixel("rgba(".implode(',', $backgroundRGB).")"), $degrees);
		}
	}

	public function save ($to) {
		if($this->useGD) {
			return $this->img->save($to);
		}
		else {
			return $this->img->writeImage($to);
		}
	}

	public function release () {
		if($this->useGD) {
			return $this->img->destroy();
		}
		else {
			return $this->img->destroy();
		}
	}
}

/**
 * A Wrapper for GD library in PHP. GD must be installed in your system for this to work.
 * Example: $img = new Image('wheel.png');
 * 			$img->flip(1)->resize(120, 0)->save('wheel.jpg');
 */
class GD_Image {
	private $file_name;
	private $info;
	public $width;
	public $height;
	private $image;
	private $org_image;

	public function getImageHeight () {
		return $this->height;
	}

	public function getImageWidth () {
		return $this->width;
	}

	/**
	 * Constructor -
	 * Arguments : Image Filepath
	 */
	public function __construct($image_file) {
		if(!file_exists($image_file) || !is_readable($image_file)) throw new Exception("$image_file doesn't exist!");

		$this->file_name = $image_file;
		$img = getimagesize($image_file);

		//Create the image depending on what kind of file it is.
		switch($img['mime']) {
			case 'image/png' : $image = imagecreatefrompng($image_file); break;
			case 'image/jpeg': $image = imagecreatefromjpeg($image_file); break;
			case 'image/gif' :
				$old_id = imagecreatefromgif($image_file);
				$image  = imagecreatetruecolor($img[0],$img[1]);
				imagecopy($image,$old_id,0,0,0,0,$img[0],$img[1]);
				break;
			default: break;
		}
		$this->info		= $img;
		$this->width	= imagesx($image);
		$this->height	= imagesy($image);
		$this->image	= $this->org_image = $image;
	}

	/**
	 * Rotates the image to any direction using the given angle.
	 * Arguments: $angle - The rotation angle, in degrees.
	 * Example: $img = new Image("file.png"); $img->rotate(180); $img->show(); // Turn the image upside down.
	 */
	public function rotate($angle, $background=0) {
		if(!$this->image) return false;
		if(!$angle) return $this;

		$this->image = imagerotate($this->image, $angle, imagecolorallocatealpha($this->image, $background[0], $background[1], $background[2], $background[3]));
		return $this;
	}

	/**
	 * Mirrors the given image in the desired way.
	 * Arguments : $type - Direction of mirroring. This can be 1(Horizondal Flip), 2(Vertical Flip) or 3(Both Horizondal and Vertical Flip)
	 * Example: $img = new Image("file.png"); $img->flip(2); $img->show();
	 */
	public function flip($type) {
		if(!$this->image) return false;
		if(!$type) return false;

		$imgdest= imagecreatetruecolor($this->width, $this->height);
		$imgsrc	= $this->image;
		$height	= $this->height;
		$width	= $this->width;

		switch( $type ) {
			//Mirroring direction
			case IMAGE_FLIP_HORIZONTAL:
			case 'h':
				for( $x=0 ; $x<$width ; $x++ )
					imagecopy($imgdest, $imgsrc, $width-$x-1, 0, $x, 0, 1, $height);
				break;

			case IMAGE_FLIP_VERTICAL:
			case 'v':
				for( $y=0 ; $y<$height ; $y++ )
					imagecopy($imgdest, $imgsrc, 0, $height-$y-1, 0, $y, $width, 1);
				break;

			case IMAGE_FLIP_BOTH:
			case 'b':
				for( $x=0 ; $x<$width ; $x++ )
					imagecopy($imgdest, $imgsrc, $width-$x-1, 0, $x, 0, 1, $height);

				$rowBuffer = imagecreatetruecolor($width, 1);
				for( $y=0 ; $y<($height/2) ; $y++ ) {
					imagecopy($rowBuffer, $imgdest  , 0, 0, 0, $height-$y-1, $width, 1);
					imagecopy($imgdest  , $imgdest  , 0, $height-$y-1, 0, $y, $width, 1);
					imagecopy($imgdest  , $rowBuffer, 0, $y, 0, 0, $width, 1);
				}

				imagedestroy( $rowBuffer );
				break;
			}

		$this->image = $imgdest;
		return $this;
	}

	/**
	 * Resize the image to an new size. Size can be specified in the arugments.
	 * Agruments :$new_width - The width of the desired image. If 0, the function will automatically calculate the width using the height ratio.
	 *			  $new_width - The width of the desired image. If 0, the function will automatically calculate the value using the width ratio.
	 *			  $use_resize- If true, the function uses imagecopyresized() function instead of imagecopyresampled().
	 *					Resize is faster but poduces poorer quality image. Resample on the other hand is slower - but makes better images.
	 * Example: $img -> resize(60, 0, false); // Better quality image created using width ratio
	 * 			$img -> resize(120, 300);
	 */
	public function resize($new_width,$new_height, $use_resize = true) {
		if(!$this->image) return false;
		if(!$new_height and !$new_width) return false; //Both width and height is 0

		$height = $this->height;
		$width  = $this->width;

		//If the width or height is give as 0, find the correct ratio using the other value
		if(!$new_height and $new_width) $new_height = $height * $new_width / $width; //Get the new height in the correct ratio
		if($new_height and !$new_width) $new_width	= $width  * $new_height/ $height;//Get the new width in the correct ratio

		//Create the image
		$new_image = imagecreatetruecolor($new_width,$new_height);
		imagealphablending($new_image, false);
		if($use_resize) imagecopyresized($new_image, $this->image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
		else imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

		$this->image = $new_image;
		return $this;
	}

	/**
	 * Crops the given image from the ($from_x,$from_y) point to the ($to_x,$to_y) point.
	 * Arguments :$from_x - X coordinate from where the crop should start
	 *			  $from_y - Y coordinate from where the crop should start
	 *			  $to_x   - X coordinate from where the crop should end
	 *			  $to_y   - Y coordinate from where the crop should end
	 * Example: $img -> crop(250,200,400,250);
	 */
	public function crop($from_x,$from_y,$to_x,$to_y) {
		if(!$this->image) return false;

		$height = $this->height;
		$width  = $this->width;

		$new_width  = $to_x - $from_x;
		$new_height = $to_y - $from_y;
		//Create the image
		$new_image = imagecreatetruecolor($new_width, $new_height);
		imagealphablending($new_image, false);
		imagecopy($new_image, $this->image, 0,0, $from_x,$from_y, $new_width, $new_height);
		$this->image = $new_image;

		return $this;
	}

	/**
	 * Save the image to the given file. You can use this function to convert image types to. Just specify the image format you want as the extension.
	 * Argument:$file_name - the file name to which the image should be saved to
	 * Returns: false if save operation fails.
	 * Example: $img->save("image.png");
	 * 			$image->save('file.jpg');
	 */
	public function save($file_name, $destroy = true) {
		if(!$this->image) return false;

		$extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

		switch($extension) {
			case 'png' : return imagepng($this->image, $file_name); break;
			case 'jpeg':
			case 'jpg' : return imagejpeg($this->image, $file_name); break;
			case 'gif' : return imagegif($this->image, $file_name); break;
			default: break;
		}
		if($destroy) $this->destroy();
		return false;
	}

	/**
	 * Display the image and then destroy it.
	 * Example: $img->show();
	 */
	public function show($destroy = true) {
		if(!$this->image) return false;

		header("Content-type: ".$this->info['mime']);
		switch($this->info['mime']) {
			case 'image/png' : imagepng($this->image); break;
			case 'image/jpeg': imagejpeg($this->image); break;
			case 'image/gif' : imagegif($this->image); break;
			default: break;
		}
		if($destroy) $this->destroy();

		return $this;
	}

	/**
	 * Discard any changes made to the image and restore the original state
	 */
	public function restore() {
		$this->image = $this->org_image;
		return $this;
	}

	/**
	 * Destroy the image to save the memory. Do this after all operations are complete.
	 */
	public function destroy() {
		 imagedestroy($this->image);
		 imagedestroy($this->org_image);
	}
}

if(extension_loaded('imagick')) {
	Image::$imagick = true;
	Image::$gd = false;
}
else if(extension_loaded('gd')) {
	Image::$gd = true;
}
else {
	throw new Exception(__('admin', 'no-gd-imagick'));
}
//phpinfo();