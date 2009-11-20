<?php

/*
# =============================================================================
# Thumbnail Class
# -----------------------------------------------------------------------------
# This PHP-Class creates thumbnail pictures for use in picture galleries
# -----------------------------------------------------------------------------
# Created by Marc Ulfig                            mail: m.ulfig@googlemail.com
#                                                      web: http://marculfig.de
#
#   This program is free software; you can redistribute it and/or modify
#   it under the terms of the GNU General Public License as published by
#   the Free Software Foundation; either version 2 of the License, or
#   (at your option) any later version.
#
#   This program is distributed in the hope that it will be useful,
#   but WITHOUT ANY WARRANTY; without even the implied warranty of
#   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#   GNU General Public License for more details.
#
#   You should have received a copy of the GNU General Public License
#   along with this program; if not, write to the Free Software
#   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# =============================================================================

*/
/*
class Thumbnail {
    
// ------------------------------------------------------------------------------    
// Konstruktor
// ------------------------------------------------------------------------------
    function Thumbnail(){
    }*/

    // ------------------------------------------------------------------------------
    // Thumbnail anlegen
    // ------------------------------------------------------------------------------
    function scaleImage($pic, $dir_origin, $dir_target, $maxWidth = false, $maxHeight = false) {
        // --------------------------------------------------------------------
        // Variablen
        // --------------------------------------------------------------------
        if($maxWidth === false or empty($maxWidth)) {
            $maxWidth  = 120; // Maximal Breite des Bildes
        }
        if($maxHeight === false or empty($maxHeight)) {
            $maxHeight = 120; // Maximal h�he des Bildes
        }
/*
if(is_file($dir_origin.$pic)) {
echo $maxWidth." x ".$maxHeight."<br>\n";
echo "file ja<br>\n";
return;
}*/


        // --------------------------------------------------------------------
        // Bildgr��e und MIME Type holen
        // --------------------------------------------------------------------
        $size    = GetImageSize($dir_origin.$pic);
        $mime    = $size['mime'];
        $width  = $size[0];
        $height = $size[1];

        // --------------------------------------------------------------------
        // Sicherheits�berpr�fungen
        // --------------------------------------------------------------------

        // Der Bildname darf folgende Zeichen nicht enthalten / : .. < >
#        if ( strpos(dirname($image), ':') || preg_match('/(\.\.|<|>)/', $image) )
         if ( strpos($pic, ':') || preg_match('/(\.\.|<|>)/', $pic) )
         {
             die("Error: Bilddatei ". $dir_origin . $pic ."enth�lt nicht g�ltige Zeichen!");
         }

        // Handelt es sich bei der Datei auch wirklich um ein Bild
        if ( substr($mime, 0, 6) != 'image/' )
        {
            die ("Error: Bei der Datei handelt es nicht um ein Bild: ". $dir_origin . $pic ." => MIMETYPE: ". $mime);
        }


        // --------------------------------------------------------------------
        // Die Seitenverh�ltnisse von Breite zu H�he und H�he zu Breite ermitteln,
        // und dann die Breite und H�he f�r das Vorschaubild ermitteln,
        // aber nur, wenn das Originalbild gr��er als das Zielbild ist
        // --------------------------------------------------------------------
#        if ($height <= $maxHeight && $width <= $maxWidth) {
#          $tnHeight = $height;
#          $tnWidth  = $width;
#        } else {
            $xRatio        = $maxWidth / $width;
            $yRatio        = $maxHeight / $height;

            if ($xRatio * $height < $maxHeight)
            { // Bildma�e auf Basis der Breite
                $tnHeight    = ceil($xRatio * $height);
                $tnWidth    = $maxWidth;
            }
            else // Bildma�e auf Basis der H�he
            {
                $tnWidth    = ceil($yRatio * $width);
                 $tnHeight    = $maxHeight;
            }
#        }
        # Bild gr�sse = Neue gr�sse also nicht zu tun
        if($tnWidth == $width and $tnHeight == $height) {
            return;
        }

        // --------------------------------------------------------------------
        // Hauptteil zum Vorschaubilder erstellen
        // --------------------------------------------------------------------

        // Welche Funktionen sollen genutzt werden um die Vorschaubilder zu erzeugen
        switch ($size['mime'])
        {
            // Damit werden GIFs verarbeitet
            case 'image/gif':
                $creationFunction    = 'ImageCreateFromGif';
                $outputFunction        = 'ImageGif';
                $doSharpen            = TRUE;
            break;
    
            // Damit werden PNGs verarbeitet
            case 'image/x-png':
            case 'image/png':
                $creationFunction    = 'ImageCreateFromPng';
                $outputFunction        = 'ImagePng';
                $doSharpen            = TRUE;
                 // PNG braucht einen Kompressionslevel 0 (Keine Kompression) bis 9 - (5 sollte ausreichen f�r Vorschaubilder)
                $quality            = 5;
            break;

            // Damit werden JPEGs verarbeitet
            case 'image/pipeg':
            case 'image/jpeg':
            case 'image/pjpeg':
                $creationFunction    = 'ImageCreateFromJpeg';
                $outputFunction         = 'ImageJpeg';
                $doSharpen            = TRUE;
                $quality            = 65;
            break;

            // alles andere wird einfach copiert (Sollte noch verbessert werden!)
            default:
                copy($dir_origin.$pic, $dir_target.$pic);
                return;
            break;
        }


        // Das Quellbild in ein Objekt laden
        $src    = $creationFunction($dir_origin.$pic);

        // Ein leeres Objekt f�r das Ziel anlegen
        $dst    = imagecreatetruecolor($tnWidth, $tnHeight);

        // Transparenz im Bild einschalten (nur GIF und PNG)
        if (in_array($size['mime'], array('image/gif', 'image/png')))
        {
            $transparencyIndex = imagecolortransparent($src);
            $transparencyColor = array('red' => 255, 'green' => 255, 'blue' => 255);
            
            if ($transparencyIndex >= 0) {
                $transparencyColor    = @imagecolorsforindex($src, $transparencyIndex);   
                $transparencyIndex    = imagecolorallocate($dst, $transparencyColor['red'], $transparencyColor['green'], $transparencyColor['blue']);
                imagefill($dst, 0, 0, $transparencyIndex);
                imagecolortransparent($dst, $transparencyIndex);

                // Wenn GIF und Transparenz gefunden, dann DO_SHARPEN ausschalten.
                // F�hrt sonst zu unsch�nen ergebnissen
                if (in_array($size['mime'], array('image/gif')))
                    $doSharpen = FALSE;
            }
        }

        // Jetzt wird das Bild in das Objekt $dst geladen und die gr��e ver�ndert
        ImageCopyResampled($dst, $src, 0, 0, 0, 0, $tnWidth, $tnHeight, $width, $height);


        // Hier wird versucht das Zielbild noch etwas sch�rfer zu bekommen
        // Das Basiert auf zwei dingen
        // 1. Die Different der Quell- und Zielgr��e
        // 2. Der Finalen Gr��e
        if ($doSharpen)
        {
            $sharpness    = findSharp($width, $tnWidth);
    
            $sharpenMatrix    = array(
                array(-1, -2, -1),
                array(-2, $sharpness + 12, -2),
                array(-1, -2, -1)
            );

            $divisor        = $sharpness;
            $offset            = 0;
            imageconvolution($dst, $sharpenMatrix, $divisor, $offset);
        }
        // Das Zielbild speichern
        $outputFunction($dst, $dir_target.$pic, $quality);
        chmod($dir_target.$pic, getChmod());

        // Aufr�umen
        ImageDestroy($src);
        ImageDestroy($dst);
    }
#}

// --------------------------------------------------------------------
// Spezielle Functionen
// --------------------------------------------------------------------

// Function von: Ryan Rud (http://adryrun.com)
function findSharp($orig, $final) {
    $final    = $final * (750.0 / $orig);
    $a        = 52;
    $b        = -0.27810650887573124;
    $c        = .00047337278106508946;
    
    $result = $a + $b * $final + $c * $final * $final;
    
    return max(round($result), 0);
} // findSharp()


// --------------------------------------------------------------------
// Diese habe ich auf php.net gefunden und hat mir bisher gute Dienste geleistet
// 
// include this file whenever you have to use imageconvolution...
// you can use in your project, but keep the comment below :)
// great for any image manipulation library
// Made by Chao Xu(Mgccl) 2/28/07
// www.webdevlogs.com
// V 1.0
if(!function_exists('imageconvolution')) {
    function imageconvolution($src, $filter, $filter_div, $offset) {
        if ($src==NULL) {
            return 0;
        }
       
        $sx = imagesx($src);
        $sy = imagesy($src);
        $srcback = ImageCreateTrueColor ($sx, $sy);
        ImageCopy($srcback, $src,0,0,0,0,$sx,$sy);
       
        if($srcback==NULL){
            return 0;
        }
           
        #FIX HERE
        #$pxl array was the problem so simply set it with very low values
        $pxl = array(1,1);
        #this little fix worked for me as the undefined array threw out errors

        for ($y=0; $y<$sy; ++$y){
            for($x=0; $x<$sx; ++$x){
                $new_r = $new_g = $new_b = 0;
                $alpha = imagecolorat($srcback, $pxl[0], $pxl[1]);
                $new_a = $alpha >> 24;
               
                for ($j=0; $j<3; ++$j) {
                    $yv = min(max($y - 1 + $j, 0), $sy - 1);
                    for ($i=0; $i<3; ++$i) {
                            $pxl = array(min(max($x - 1 + $i, 0), $sx - 1), $yv);
                        $rgb = imagecolorat($srcback, $pxl[0], $pxl[1]);
                        $new_r += (($rgb >> 16) & 0xFF) * $filter[$j][$i];
                        $new_g += (($rgb >> 8) & 0xFF) * $filter[$j][$i];
                        $new_b += ($rgb & 0xFF) * $filter[$j][$i];
                    }
                }

                $new_r = ($new_r/$filter_div)+$offset;
                $new_g = ($new_g/$filter_div)+$offset;
                $new_b = ($new_b/$filter_div)+$offset;

                $new_r = ($new_r > 255)? 255 : (($new_r < 0)? 0:$new_r);
                $new_g = ($new_g > 255)? 255 : (($new_g < 0)? 0:$new_g);
                $new_b = ($new_b > 255)? 255 : (($new_b < 0)? 0:$new_b);

                $new_pxl = ImageColorAllocateAlpha($src, (int)$new_r, (int)$new_g, (int)$new_b, $new_a);
                if ($new_pxl == -1) {
                    $new_pxl = ImageColorClosestAlpha($src, (int)$new_r, (int)$new_g, (int)$new_b, $new_a);
                }
                if (($y >= 0) && ($y < $sy)) {
                    imagesetpixel($src, $x, $y, $new_pxl);
                }
            }
        }
        imagedestroy($srcback);
        return 1;
    }
}
?>
