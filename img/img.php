<?
// Получаем параметры цвета
$rgb = (isset($_REQUEST['rgb'])) ? $_REQUEST['rgb'] : '000000';

$m = 0;
if (preg_match('|^(..)(..)(..)$|', $rgb, $m))	{
	$col_r = hexdec('0x'.$m[1]);
	$col_g = hexdec('0x'.$m[2]);
	$col_b = hexdec('0x'.$m[3]);
}	else	{
	$col_r = 0;
	$col_g = 0;
	$col_b = 0;
}


// Создаём пустое изображение и добавляем текст
$im    = imagecreatetruecolor(1, 1);
$color = imagecolorallocate($im, $col_r, $col_g, $col_b);
imageline($im, 0, 0, 0, 0, $color);


// Устанавливаем тип содержимого в заголовок, в данном случае image/jpeg
header('Content-Type: image/jpeg');

// Выводим изображение
imagejpeg($im);

// Освобождаем память
imagedestroy($im);
