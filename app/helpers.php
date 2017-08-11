<?php

/**
 * So that we don't have a lot of files/images in one folder, this function
 * is used to convert a filename, such as myimage.png, into m/y/i/myimage.png.
 *
 * Many Amazon S3 browsers will treat these as folders which is useful if we
 * manually browse the filestore.
*/
if (!function_exists('split_to_path'))
{
    /**
     * Split a given filename's first 3 characters to directories.
     *
     * @param string $filename
     * 
     * @return string
     */
    function split_to_path($filename)
    {
        $f = str_split($filename, 1);
        return $f[0] . '/' . $f[1] . '/' . $f[2] . '/' . $filename;
    }
}
