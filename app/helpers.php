<?php

	// splits a provided string and returns the first three characters separated by "/". (for example, "testing" becomes "t/e/s".
	function split_filename( $filename )
	{
		$h = $filename;
		if( strlen($h) < 4 )
			return false;

		return $h[0] . '/' . $h[1] . '/' . $h[2] . '/' . $h;
	}


	function file_extension( $path )
	{
		$p = explode('.', $path);
		return $p[count($p)-1];
	}

// generates a thumbnail from an image and returns it.
function generateThumb($filename)
{
	// Vars:
	//Need our image type
	$image_ext = strtolower(file_extension($filename));

	// We need our width, height, type and other attributes of our source image.
	list($source_width, $source_height, $type, $attr) = getimagesize($filename) or $error = true;

	// Get total pixels so we don't run out of memory
	$total_pixels = $source_width * $source_height;

	// If we fail to read this image it's unreadable, if we have no pixels, it's a failure!
	if(isset($error) || $total_pixels == 0)
	{
		// We had a problem reading this image
		return false;
	}

	/* Now we need to do some Imagick management */

	// 120 Million pixel limit because Imagick is awesome
	$pixel_limit = 120000000;

	// If we're under our pixel limit...
	if($total_pixels <= $pixel_limit)
	{
		try
		{
			// Define our 'ideal' thumbnail dimensions for a 16:10 thumbnail
			$width = 200; // Width of 200
			$height = 125; // Height of 125

			// Load the image as an Imagick object
			$im = new Imagick($filename);

			// Get the current image dimensions
			$geo = $im->getImageGeometry();

			// If our source image's dimensions are both bigger than our thumb's dimensions are going to be
			if(($geo['width'] >= $width) && ($geo['height'] >= $height))
			{
				if($image_ext == "gif")
				{
					// For GIFs, reset the iterator so we get the initial frame instead of the final one
					$im->resetIterator();

					// Crop the thumbnail image bro
					$im->cropThumbnailImage( $width, $height );
				}
				else
				{
					// Formatting our image to fit within our required dimensions and retain its aspect ratio
					if(($geo['width']/$width) <= ($geo['height']/$height))
					{
						$im->cropImage($geo['width'], floor($height*$geo['width']/$width), 0, (($geo['height']-($height*$geo['width']/$width))/2));
					}
					else
					{
						$im->cropImage(ceil($width*$geo['height']/$height), $geo['height'], (($geo['width']-($width*$geo['height']/$height))/2), 0);
					}

					// Set format
					$im->setImageFormat($image_ext);

					// Otherwise normal
					$im->ThumbnailImage($width, $height, true);
				}

				// save the image blob
				$return = $im->getImageBlob();

				// Destroy the existing thumb from memory
				$im->destroy();

				// Return blob
				return $return;
			}
			else
			{
				if($image_ext == "gif")
				{
					// For GIFs, reset the iterator so we get the initial frame instead of the final one
					$im->resetIterator();

					// Crop the thumbnail image bro
					$im->cropThumbnailImage( $width, $height );
				}

				else
				{
					// Formatting our image to fit within our required dimensions and retain its aspect ratio
					if(($geo['width']/$width) <= ($geo['height']/$height))
					{
							$im->cropImage($geo['width'], floor($height*$geo['width']/$width), 0, (($geo['height']-($height*$geo['width']/$width))/2));
					}
					else
					{
							$im->cropImage(ceil($width*$geo['height']/$height), $geo['height'], (($geo['width']-($width*$geo['height']/$height))/2), 0);
					}

					// Set format
					$im->setImageFormat($image_ext);

					// Otherwise normal
					$im->ThumbnailImage($width, $height, true);
				}

				// save the thumbnail blob
				$return = $im->getImageBlob();

				// Destroy the existing thumb from memory
				$im->destroy();

				// Return blob
				return $return;
			}
		}
		catch(Exception $e)
		{
			// An error has occurred that we couldn't recover from.
			return false;
		}
	}
	else
	{
		// They were over the pixel limit, give them a stock thumbnail
		return file_read_contents( env('NO_THUMB_IMAGE') );
	}
}
