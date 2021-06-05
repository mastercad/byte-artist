<?php

namespace App\Service\Filter\Ubb;

use App\Service\DOM\TagMerge;
use App\Service\File;
use GeSHi;

class Replace
{
    /**
     * string Key for regex in config object.
     */
    public const REGEX = 'regex';

    /**
     * string Key for replace function in config object.
     */
    public const REPLACE_FUNCTION = 'replace_function';

    /**
     * @var array enhält alle erlaubten tags, die replaced werden sollen
     */
//    protected $allowdTags = array(
//        '[QUOTE=]' => ['[QUOTE=]', '[/QUOTE]'],
//        '[QUOTE]' => ['[QUOTE]', '[/QUOTE]'],
//        '[GALLERY]' => ['[GALLERY]', '[/GALLERY]'],
//        '[IMG]' => ['[IMG]', '[/IMG]'],
//        '[IMG=]' => ['[IMG:]', '[/IMG]'],
//        '[EMAIL]' => ['[EMAIL]', '[/EMAIL'],
//        '[EMAIL=]' => ['[EMAIL=]', '[/EMAIL]'],
//        '[URL]' => ['[URL]', '[/URL]'],
//        '[URL=]' => ['[URL=]', '[/URL]'],
//        '[URLIMG=]' => ['[URLIMG=]', '[/URLIMG]'],
//        '[PHP]' => ['[PHP]', '[/PHP]'],
//        '[CODE=]' => ['[CODE=]', '[/CODE]'],
//        '[MITGLIED]' => '[MITGLIED]', '[/MITGLIED]',
//        '[MEMBER]' => '[MEMBER]', '[/MEMBER]',
//        '[LINIE=]' => ['[LINIE]', ''],
//        '[ULISTE]' => ['[ULISTE]', '[/ULISTE]'],
//        '[DANKE]' => ['[DANKE]', '[/DANKE]'],
//        '[THANKS]' => ['[THANKS]', '[/THANKS]'],
//        '[BLOCK]' => ['[BLOCK]', '[/BLOCK]'],
//        '[CENTER]' => ['[CENTER]', '[/CENTER]'],
//        '[LEFT]' => ['[LEFT]', '[/LEFT]'],
//        '[RIGHT]' => ['[RIGHT]', '[/RIGHT]'],
//        '[FLOAT]' => ['[FLOAT]', '[/FLOAT]'],
//        '[MARQUEE]' => ['[MARQUEE]', '[/MARQUEE]'],
//        '[NL]' => ['[NL]', ''],
//        '[VIDEO]' => ['[VIDEO]', '[/VIDEO]'],
//        '[COLOR=]' => ['[COLOR]', '[/COLOR]'],
//        '[BGCOLOR=]' => ['[BGCOLOR]', '[/BGCOLOR]'],
//        '[SIZE=]' => ['[SIZE]', '[/SIZE]'],
//        '[GLOW]' => ['[GLOW]', '[/GLOW]'],
//        '[WAVE]' => ['[WAVE]', '[/WAVE]'],
//        '[SHADOW=]' => ['[SHADOW=]', '[/SHADOW]'],
//        '[I]' => ['[I]', '[/I]'],
//        '[B]' => ['[B]', '[/B]'],
//        '[U]' => ['[U]', '[/U]'],
//        '[S]' => ['[S]', '[/S]'],
//        '[FONT=]' => ['[FONT]', '[/FONT]'],
//        '[NEWS]' => ['[NEWS]', ''],
//        '[H1]' => ['[H1]', '[/H1]'],
//        '[H2]' => ['[H2]', '[/H2]'],
//        '[H3]' => ['[H3]', '[/H3]'],
//        '[H4]' => ['[H4]', '[/H4]'],
//        '[H5]' => ['[H5]', '[/H5]'],
//        '[H6]' => ['[H6]', '[/H6]'],
//        '[TABLE]' => ['[TABLE]', '[/TABLE]'],
//        '[LEGEND]' => ['[LEGEND]'],
//    );

    protected $allowdTags = [
        '[QUOTE=]',
        '[QUOTE]',
        '[GALLERY]',
        '[IMG]',
        '[IMG=]',
        '[EMAIL]',
        '[EMAIL=]',
        '[URL]',
        '[URL=]',
        '[URLIMG=]',
        '[PHP]',
        '[CODE=]',
        '[MITGLIED]',
        '[MEMBER]',
        '[LINIE=]',
        '[ULISTE]',
        '[DANKE]',
        '[THANKS]',
        '[BLOCK]',
        '[CENTER]',
        '[LEFT]',
        '[RIGHT]',
        '[FLOAT]',
        '[MARQUEE]',
        '[NL]',
        '[VIDEO]',
        '[COLOR=]',
        '[BGCOLOR=]',
        '[SIZE=]',
        '[GLOW]',
        '[WAVE]',
        '[SHADOW=]',
        '[I]',
        '[B]',
        '[U]',
        '[S]',
        '[FONT=]',
        '[NEWS]',
        '[H1]',
        '[H2]',
        '[H3]',
        '[H4]',
        '[H5]',
        '[H6]',
        '[TABLE]',
        '[LEGEND]',
    ];

    /**
     * @var array enthält das Tag und dazu den regex sowie den funktionsaufruf
     */
    protected $aMapTagToFunction = [
        '[QUOTE=]' => [
            self::REGEX => '/(\[QUOTE=)(.*?)(\])(.*?)(\[\/QUOTE\])/is',
            self::REPLACE_FUNCTION => 'generateQuote',
        ],
        '[QUOTE]' => [
            self::REGEX => '/(\[QUOTE\])(.*?)(\[\/QUOTE\])/is',
            self::REPLACE_FUNCTION => 'generateQuote',
        ],
        '[GALLERY]' => [
            self::REGEX => '/(\[GALLERY\])(.*?)(\[\/GALLERY\])/is',
            self::REPLACE_FUNCTION => 'generateGallery',
        ],
        '[IMG]' => [
            self::REGEX => '/(\[IMG\])([^\[]+)?(\[\/IMG\])/is',
            self::REPLACE_FUNCTION => 'generateImage',
        ],
        '[IMG=]' => [
            self::REGEX => '/(\[IMG[=|:])([^\]]+)?(\])(.*?)(\[\/IMG\])/is',
            self::REPLACE_FUNCTION => 'generateImage',
        ],
        '[IMG:]' => [
            self::REGEX => '/(\[IMG[=|:])([^\]]+)?(\])(.*?)(\[\/IMG\])/is',
            self::REPLACE_FUNCTION => 'generateImage',
        ],
        '[EMAIL]' => [
            self::REGEX => '/(\[EMAIL\])(.*?)(\[\/EMAIL\])/Uis',
            self::REPLACE_FUNCTION => 'generateMail',
        ],
        '[EMAIL=]' => [
            self::REGEX => '/(\[EMAIL=)([^\[]+)?(\])(.*?)(\[\/EMAIL\])/is',
            self::REPLACE_FUNCTION => 'generateMail',
        ],
        '[URL]' => [
            self::REGEX => '/(\[URL\])([^\[]+)(\[\/URL\])/Uis',
            self::REPLACE_FUNCTION => 'generateUrl',
        ],
        '[URL=]' => [
            self::REGEX => '/(\[URL=)([^\[]+)?(\])([^\[]+)?(\[\/URL\])/is',
            self::REPLACE_FUNCTION => 'generateUrl',
        ],
        '[URLIMG=]' => [
            self::REGEX => '/(\[URLIMG=)(.*)?(\])([^\[]+?)(\[\/URLIMG\])/is',
            self::REPLACE_FUNCTION => 'generateUrlImage',
        ],
        '[PHP]' => [
            self::REGEX => '/(\[PHP\])(.*?)(\[\/PHP\])/is',
            self::REPLACE_FUNCTION => 'replacePhpCode',
        ],
        '[CODE=]' => [
            self::REGEX => '|(\[CODE=)(.*?)(\])(.*?)(\[\/CODE\])|is',
            self::REPLACE_FUNCTION => 'replaceCode',
        ],
        '[MITGLIED]' => [
            self::REGEX => '/(\[MITGLIED\])(.*?)(\[\/MITGLIED\])/is',
            self::REPLACE_FUNCTION => 'memberText',
        ],
        '[MEMBER]' => [
            self::REGEX => '/(\[MEMBER\])(.*?)(\[\/MEMBER\])/is',
            self::REPLACE_FUNCTION => 'memberText',
        ],
        '[LINIE=]' => [
            self::REGEX => '/(\[LINIE=)(.*?)(\])/Uis',
            self::REPLACE_FUNCTION => 'generateLine',
        ],
        '[ULISTE]' => [
            self::REGEX => '/(\[ULISTE\])(.*?)(\[\/ULISTE\])/is',
            self::REPLACE_FUNCTION => 'replaceList',
        ],
        '[DANKE]' => [
            self::REGEX => '/(\[DANKE\])(.*?)(\[\/DANKE\])/is',
            self::REPLACE_FUNCTION => 'replaceThanksText',
        ],
        '[THANKS]' => [
            self::REGEX => '/(\[THANKS\])(.*?)(\[\/THANKS\])/is',
            self::REPLACE_FUNCTION => 'replaceThanksText',
        ],
        '[BLOCK]' => [
            self::REGEX => '/(\[BLOCK\])(.*?)(\[\/BLOCK\])/is',
            self::REPLACE_FUNCTION => 'replaceBlock',
        ],
        '[CENTER]' => [
            self::REGEX => '/(\[CENTER\])(.*?)(\[\/CENTER\])/is',
            self::REPLACE_FUNCTION => 'replaceCenter',
        ],
        '[LEFT]' => [
            self::REGEX => '/(\[LEFT\])(.*?)(\[\/LEFT\])/is',
            self::REPLACE_FUNCTION => 'replaceLeft',
        ],
        '[RIGHT]' => [
            self::REGEX => '/(\[RIGHT\])(.*?)(\[\/RIGHT\])/is',
            self::REPLACE_FUNCTION => 'replaceRight',
        ],
        '[FLOAT]' => [
            self::REGEX => '/(\[FLOAT\])(.*?)(\[\/FLOAT\])/is',
            self::REPLACE_FUNCTION => 'replaceFloat',
        ],
        '[MARQUEE]' => [
            self::REGEX => '/(\[MARQUEE\])(.*?)(\[\/MARQUEE\])/is',
            self::REPLACE_FUNCTION => 'replaceMarquee',
        ],
        '[NL]' => [
            self::REGEX => '/(\[NL\])/Ui',
            self::REPLACE_FUNCTION => 'replaceNewLine',
        ],
        '[VIDEO]' => [
//            self::REGEX => '/(\[VIDEO\])(.*?)(\[\/VIDEO\])/i',
            self::REGEX => '/(\[(VIDEO:|VIDEO=)([^\]]*)\]|\[VIDEO\])([^(\[\\)]*)\[\/(VIDEO)\]/i',
            self::REPLACE_FUNCTION => 'addVideo',
        ],
        '[COLOR=]' => [
            self::REGEX => '/(\[COLOR=)(#+[0-9a-f]{3,}|[A-Z]{3,})(\])(.*?)(\[\/COLOR\])/is',
            self::REPLACE_FUNCTION => 'replaceColor',
        ],
        '[BGCOLOR=]' => [
            self::REGEX => '/(\[BGCOLOR=)(#+[0-9a-f]{3,}|[A-Z]{3,})(\])(.*?)(\[\/BGCOLOR\])/is',
            self::REPLACE_FUNCTION => 'replaceBackgroundColor',
        ],
        '[SIZE=]' => [
            self::REGEX => '/(\[SIZE=)([0-9]{1,})(\])(.*?)(\[\/SIZE\])/is',
            self::REPLACE_FUNCTION => 'replaceSize',
        ],
        '[GLOW]' => [
            self::REGEX => '/(\[GLOW\])(.*?)(\[\/GLOW\])/is',
            self::REPLACE_FUNCTION => 'replaceGlow',
        ],
        '[WAVE]' => [
            self::REGEX => '/(\[WAVE\])(.*?)(\[\/WAVE])/is',
            self::REPLACE_FUNCTION => 'replaceWave',
        ],
        '[SHADOW=]' => [
            self::REGEX => '/(\[SHADOW=)(#+[0-9a-f]{3,}|[A-Z]{3,})(\])(.*?)(\[\/SHADOW\])/is',
            self::REPLACE_FUNCTION => 'replaceShadow',
        ],
        '[I]' => [
            self::REGEX => '/(\[I\])(.*?)(\[\/I\])/is',
            self::REPLACE_FUNCTION => 'replaceItalic',
        ],
        '[B]' => [
            self::REGEX => '/(\[B\])(.*?)(\[\/B\])/is',
            self::REPLACE_FUNCTION => 'replaceBold',
        ],
        '[U]' => [
            self::REGEX => '/(\[U\])(.*?)(\[\/U\])/is',
            self::REPLACE_FUNCTION => 'replaceUnderlined',
        ],
        '[S]' => [
            self::REGEX => '/(\[S\])(.*?)(\[\/S\])/is',
            self::REPLACE_FUNCTION => 'replaceSmall',
        ],
        '[FONT=]' => [
            self::REGEX => '/(\[FONT=)(.*?)(\])(.*?)(\[\/FONT\])/is',
            self::REPLACE_FUNCTION => 'replaceFont',
        ],
        '[NEWS]' => [
            self::REGEX => '/(\[NEWS\])/i',
            self::REPLACE_FUNCTION => 'getNews',
        ],
        '[H1]' => [
            self::REGEX => '/(\[H([0-9]{1})\])(.*?)(\[\/H\2\])/i',
            self::REPLACE_FUNCTION => 'replaceHeadline',
        ],
        '[H2]' => [
            self::REGEX => '/(\[H([0-9]{1})\])(.*?)(\[\/H\2\])/i',
            self::REPLACE_FUNCTION => 'replaceHeadline',
        ],
        '[H3]' => [
            self::REGEX => '/(\[H([0-9]{1})\])(.*?)(\[\/H\2\])/i',
            self::REPLACE_FUNCTION => 'replaceHeadline',
        ],
        '[H4]' => [
            self::REGEX => '/(\[H([0-9]{1})\])(.*?)(\[\/H\2\])/i',
            self::REPLACE_FUNCTION => 'replaceHeadline',
        ],
        '[H5]' => [
            self::REGEX => '/(\[H([0-9]{1})\])(.*?)(\[\/H\2\])/i',
            self::REPLACE_FUNCTION => 'replaceHeadline',
        ],
        '[H6]' => [
            self::REGEX => '/(\[H([0-9]{1})\])(.*?)(\[\/H\2\])/i',
            self::REPLACE_FUNCTION => 'replaceHeadline',
        ],
        '[TABLE]' => [
            self::REGEX => '/(\[TABLE\])(.*?)(\[\/TABLE\])/ims',
            self::REPLACE_FUNCTION => 'generateTable',
        ],
        '[LEGEND]' => [
            self::REGEX => '/(.*?\[LEGEND\].*)/ims',
            self::REPLACE_FUNCTION => 'generateLegend',
        ],
    ];

    private $removeDeniedTags = false;

    private $br_cleart;

    private $str_bilder_pfad;

    private $str_temp_bilder_pfad;

    private $bWithoutLinks = false;
    private $solveSourceInformation = false;

    private static $replaceSmilies;

    /**
     * @param bool $br_cleart
     */
    public function __construct($br_cleart = true)
    {
//        ini_set('display_errors', 1);
//        ini_set('display_startup_errors', 1);

//        error_reporting(E_ALL | E_STRICT);

        $this->br_cleart = $br_cleart;
    }

    /**
     * @var string
     *
     * @return string
     */
    public function filter($sText)
    {
//        $sText = htmlentities($sText, null, 'UTF-8');

//        foreach ($this->allowdTags as $sAllowedTag => $params) {
        $tagsFound = false;
        foreach ($this->allowdTags as $sAllowedTag) {
            if (array_key_exists($sAllowedTag, $this->aMapTagToFunction)) {
                $aCurrentMap = $this->aMapTagToFunction[$sAllowedTag];
                $sTempText = $sText;
                $sText = preg_replace_callback(
                    $aCurrentMap[self::REGEX],
                    [&$this, $aCurrentMap[self::REPLACE_FUNCTION]],
                    $sText
                );

                // Fallback to prevent double replacing of linebreaks in "no-ubb content"
                if ($sTempText !== $sText) {
                    $tagsFound = true;
                }
            }
        }

        if (!$tagsFound) {
            return $sText;
        }

        $sText = $this->br_cleart ?
            preg_replace_callback('([\n\r|\n])', [&$this, 'replaceNewLine'], $sText) :
            $sText = nl2br($sText);

//            $sText = preg_replace_callback( '([\n\r|\n])', array(&$this, "replaceLineBreak"), $sText);
//
        $sText = preg_replace_callback("/([\t])/", [&$this, 'replaceTab'], $sText);

//        $sText = $this->smileyReplace($sText);

        $oCadMerge = new TagMerge();

        return $oCadMerge->merge($sText);
    }

    public function replaceTab()
    {
        return '&nbsp;&nbsp;&nbsp;&nbsp;';
    }

    public function replaceLineBreak()
    {
        return '<br />';
    }

    public function replaceSmall($aMatches)
    {
        return '<s>'.$aMatches[1].'</s>';
    }

    public function replaceNewLine()
    {
        return '<br class="clearfix" />';
    }

    public function replaceMarquee($aMatches)
    {
        return '<marquee>'.$aMatches[2].'</marquee>';
    }

    public function replaceFloat($aMatches)
    {
        return '<div style="float: left; display: inline;">'.$aMatches[2].'</div>';
    }

    public function replaceRight($aMatches)
    {
        return '<div style="text-align: right;">'.$aMatches[2].'<br style="clear: both;" /></div>';
    }

    public function replaceLeft($aMatches)
    {
        return '<div style="text-align: left;">'.$aMatches[2].'<br style="clear: both;" /></div>';
    }

    public function replaceCenter($aMatches)
    {
        return '<div style="text-align: center;">'.$aMatches[2].'<br style="clear: both;" /></div>';
    }

    public function replaceBlock($aMatches)
    {
        return '<div style="text-align: justify;">'.$aMatches[2].'<br style="clear: both;" /></div>';
    }

    public function replaceThanksText($aMatches)
    {
        return $this->dankeText($aMatches[2]);
    }

    public function replaceList($aMatches)
    {
        return $this->erstelleListe($aMatches[2]);
    }

    public function replaceHeadline($aMatches)
    {
        return '<h'.$aMatches[2].'>'.$aMatches[3].'</h'.$aMatches[2].'>';
    }

    public function replaceColor($mInput)
    {
        $sRegEx = $this->aMapTagToFunction['[COLOR=]'][self::REGEX];

        if (is_array($mInput)) {
            $mInput = '<span style="color: '.$mInput[2].';">'.$mInput[4].'</span>';
        }

        return preg_replace_callback($sRegEx, [&$this, 'replaceColor'], $mInput);
    }

    public function replaceBackgroundColor($mInput)
    {
        $sRegEx = $this->aMapTagToFunction['[BGCOLOR=]'][self::REGEX];

        if (is_array($mInput)) {
            $mInput = '<span style="background-color: '.$mInput[2].';">'.$mInput[4].'</span>';
        }

        return preg_replace_callback($sRegEx, [&$this, 'replaceBackgroundColor'], $mInput);
    }

    public function replaceBold($mInput)
    {
        $sRegEx = $this->aMapTagToFunction['[B]'][self::REGEX];

        if (is_array($mInput)) {
            $mInput = '<span style="font-weight: bold;">'.$mInput[2].'</span>';
        }

        return preg_replace_callback($sRegEx, [&$this, 'replaceBold'], $mInput);
    }

    public function replaceItalic($mInput)
    {
        $sRegEx = $this->aMapTagToFunction['[I]'][self::REGEX];

        if (is_array($mInput)) {
            $mInput = '<span style="font-style: italic;">'.$mInput[2].'</span>';
        }

        return preg_replace_callback($sRegEx, [&$this, 'replaceItalic'], $mInput);
    }

    public function replaceUnderlined($mInput)
    {
        $sRegEx = $this->aMapTagToFunction['[U]'][self::REGEX];

        if (is_array($mInput)) {
            $mInput = '<span style="text-decoration: underline;">'.$mInput[2].'</span>';
        }

        return preg_replace_callback($sRegEx, [&$this, 'replaceUnderlined'], $mInput);
    }

    public function replaceCode($mInput)
    {
        $sRegEx = $this->aMapTagToFunction['[CODE=]'][self::REGEX];

        if (is_array($mInput)) {
            $mInput = $this->codeString($mInput[2], $mInput[4]);
        }

        return preg_replace_callback($sRegEx, [&$this, 'replaceCode'], $mInput);
    }

    public function replacePhpCode($mInput)
    {
        $sRegEx = $this->aMapTagToFunction['[PHP]'][self::REGEX];

        if (is_array($mInput)) {
            $mInput = $this->codeString('php', $mInput[2]);
        }

        return preg_replace_callback($sRegEx, [&$this, 'replaceCode'], $mInput);
    }

    public function replaceFont($mInput)
    {
        $sRegEx = $this->aMapTagToFunction['[FONT=]'][self::REGEX];

        if (is_array($mInput)) {
            $mInput = '<span style="font-family: '.$mInput[2].', Helvetica, Arial, Verdana, Tahoma;">'.$mInput[4].'</span>';
        }

        return preg_replace_callback($sRegEx, [&$this, 'replaceFont'], $mInput);
    }

    public function replaceSize($mInput)
    {
        $sRegEx = $this->aMapTagToFunction['[SIZE=]'][self::REGEX];

        if (is_array($mInput)) {
            $mInput = '<span style="font-size: '.$mInput[2].'px;">'.$mInput[4].'</span>';
        }

        return preg_replace_callback($sRegEx, [&$this, 'replaceSize'], $mInput);
    }

    public function replaceGlow($mInput)
    {
        $sRegEx = $this->aMapTagToFunction['[GLOW]'][self::REGEX];

        if (is_array($mInput)) {
            $mInput = '<GLOW>'.$mInput[2].'</GLOW>';
        }

        return preg_replace_callback($sRegEx, [&$this, 'replaceWave'], $mInput);
    }

    public function replaceWave($mInput)
    {
        $sRegEx = $this->aMapTagToFunction['[WAVE]'][self::REGEX];

        if (is_array($mInput)) {
            $mInput = '<WAVE>'.$mInput[2].'</WAVE>';
        }

        return preg_replace_callback($sRegEx, [&$this, 'replaceWave'], $mInput);
    }

    public function replaceShadow($mInput)
    {
        $sRegEx = $this->aMapTagToFunction['[SHADOW=]'][self::REGEX];

        if (is_array($mInput)) {
            $mInput = '<span style="box-shadow: 5px 5px 15px '.$mInput[2].';">'.$mInput[4].'</span>';
        }

        return preg_replace_callback($sRegEx, [&$this, 'replaceShadow'], $mInput);
    }

    public function generateQuote($aMatches)
    {
        if (6 == count($aMatches)) {
            return '<div class="quote" ><div class="quote_kopf"> Zitat von : '.$aMatches[2].
                    '</div><div class="quote_inhalt">'.$aMatches[4].'</div></div>';
        } else {
            return '<div class="quote" ><div class="quote_kopf"> Zitat :</div><div class="quote_inhalt">'.
                    $aMatches[2].'</div></div>';
        }
    }

    public function generateMail($aMatches)
    {
        if (6 == count($aMatches)) {
            return '<a href="mailto:'.$aMatches[4].'">'.$aMatches[2].'</a>';
        } else {
            return '<a href="mailto:'.$aMatches[2].'">'.$aMatches[2].'</a>';
        }
    }

    public function generateTable($aMatches)
    {
        $rows = preg_split('/\r\n|\n/', $aMatches[2]);
        $content = '<table class="cad-table">';
        foreach ($rows as $row) {
            $content .= '<tr class="cad-table-row">';
            $cells = preg_split('/\|/', $row);
            foreach ($cells as $cell) {
                $content .= '<td class="cad-table-cell">'.$cell.'</td>';
            }
            $content .= '</tr>';
        }
        $content .= '</table>';

        return $content;
    }

    public function generateImage($aMatches)
    {
        if (6 == count($aMatches)) {
            return $this->imageEinfuegenNeu($aMatches[4], $aMatches[2]);
        } else {
            return $this->imageEinfuegen($aMatches[2]);
        }
    }

    public function generateLine($aMatches)
    {
        return '<hr style="width: 100%; height: 2px; color: '.$aMatches[2].'; background: '.$aMatches[2].
            '; margin: 10px 0px; border: 0;" />';
    }

    public function generateUrl($aMatches)
    {
        if (6 == count($aMatches)) {
            return $this->ersetzeUrl($aMatches[4], $aMatches[2]);
        } else {
            return $this->ersetzeUrl($aMatches[2]);
        }
    }

    public function generateUrlImage($aMatches)
    {
        return $this->ersetzeUrlImage($aMatches[4], $aMatches[2]);
    }

    public function generateGallery($aMatches)
    {
        if (preg_match_all($this->aMapTagToFunction['[IMG=]'][static::REGEX], $aMatches[2], $thumbs)) {
            $content = '<div class="cad-gallery" style="width: 100%; height: 200px; font-size: 0; padding-top: 5px; '.
                'padding-left: 5px;">';
            foreach ($thumbs[0] as $thumbNumber => $thumb) {
                $content .= $this->imageEinfuegenNeu($thumbs[4][$thumbNumber], $thumbs[2][$thumbNumber], false);
            }
            $content .= '</div>';

            return $content;
        }

        return $aMatches[2];
    }

    public function generateLegend($aMatches)
    {
        $content = $aMatches[1];
        $legend = '';
        if (preg_match_all('/(\<H([0-6])\>)(.*?)(\<\/H\2\>)/i', $content, $headings)) {
            $legend = '<div style="margin: 15px;"><p><strong>Legende :</strong></p>';
            foreach ($headings[0] as $pos => $baseHeading) {
                $currentLevel = $headings[2][$pos];
                $anchorName = preg_replace('/\s/', '', $headings[3][$pos]);
                $legend .= '<a href="#'.$anchorName.'" style="margin-left: '.(($currentLevel + 1) * 5).'px;">'.
                    $headings[3][$pos].'</a><br />';
                $anchor = '<a name="'.$anchorName.'"></a>';
                $regex = str_replace(
                    ['<', '>', '/', '(', ')', '[', ']', '{', '}', '*', ':', '.'],
                    ['\<', '\>', '\/', '\(', '\)', '\[', '\]', '\{', '\}', '\*', '\:', '\.'],
                    $headings[0][$pos]
                );
                $content = preg_replace('/'.$regex.'/ms', $anchor.$headings[0][$pos], $content);
            }
            $legend .= '</div>';
        }
        $content = preg_replace('/\[LEGEND\]/i', $legend, $content);

        return $content;
    }

    /**
     * @todo weitere weichen und HTML5 Code für embedded videos implementieren
     * ich brauchte jetzt erstmal nur youtube :D
     *
     * @param array $aMatches
     *
     * Group1 : Video Tag and possible Options
     * Group2 : Video Tag and Separator (:|= or nothing)
     * Group3 : possible Options
     * Group4 : VideoURL
     * Group5 : Video End Tag
     *
     * @return string
     */
    public function addVideo($aMatches)
    {
        if (true === is_array($aMatches) && array_key_exists(4, $aMatches)
        ) {
            $sVideoUrl = $aMatches[4];
            if (preg_match('/^http[s]{0,1}:\/\/www\.youtube\..*/i', $sVideoUrl)) {
                return $this->addYoutubeVideo($sVideoUrl, isset($aMatches[3]) ? $aMatches[3] : '');
            }
            if (preg_match('/^http[s]{0,1}:\/\/youtu\..*/i', $sVideoUrl)) {
                return $this->addYoutubeVideo($sVideoUrl, isset($aMatches[3]) ? $aMatches[3] : '');
            }

            return '<script type="text/javascript" '.
                        'src="http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>'.
                    '<script type="text/javascript" src="/js/jwplayer.js"></script>'.
                    '<embed flashvars="file=$1&autostart=false" allowfullscreen="true" '.
                    'allowscripaccess="always" id="player1" name="player1" src="'.$sVideoUrl.'" '.
                    'width="480" height="270"/>';
        }

        return '';
    }

    public function addYoutubeVideo($videoUrl, $options = null)
    {
        $videoId = $this->parseYoutubeVideoUrl($videoUrl);
        $videoOptions = $this->parseOptions($options);

        $videoContent = '<div style="text-align:center; width: 100%;">'.
                '<iframe width="560" height="315" src="https://www.youtube.com/embed/'.$videoId.'" '.
                ' frameborder="0" allowfullscreen ></iframe>';

        if (is_array($videoOptions)
            && array_key_exists('name', $videoOptions)
            && 0 < strlen(trim($videoOptions['name']))
        ) {
            $videoContent .= '<p style="clear: both; margin-top: 15px; font-style: italic;">Quelle: Youtube, Titel: '.
                trim($videoOptions['name']).' </p>';
        }
        $videoContent .= '</div>';

        return $videoContent;
    }

    private function parseOptions($optionsString)
    {
        $rawOptions = explode('|', $optionsString);
        $options = [];

        foreach ($rawOptions as $option) {
            $optionData = explode('=', $option);

            if (2 == count($optionData)) {
                $options[$optionData[0]] = $optionData[1];
            }
        }

        return $options;
    }

    private function parseYoutubeVideoUrl($sVideoUrl)
    {
        $sVideoId = $sVideoUrl;

        if (preg_match('/youtu\.be\/(.*)/i', $sVideoUrl, $aMatches)) {
            $sVideoId = $aMatches[1];
        } elseif (preg_match('/youtube.*[\?|\&]v=([\-\_A-Za-z0-9]{1,})/i', $sVideoUrl, $aMatches)) {
            $sVideoId = $aMatches[1];
        } elseif (preg_match('/youtube.*?v=(.*?)/i', $sVideoUrl, $aMatches)) {
            $sVideoId = $aMatches[1];
        }

        return trim($sVideoId);
    }

    public function addKnownVideoFormat($aMatches)
    {
    }

    public function addUnknownVideoFormat($aMatches)
    {
    }

    /*
     * Funktion zum ersetzen der URLs, ist ein URL außerhalb des lokalen
     * Servers, wird das Link Tag automatisch mit einem Target _blank versehen
     */

    private function ersetzeUrl($url, $name = '')
    {
        $link = '';
        $hostname_url = parse_url($url, PHP_URL_HOST);
        $hostname_server = 'byte-artist.de';

        if (is_array($_SERVER) &&
                array_key_exists('SERVER_NAME', $_SERVER)) {
            $hostname_server = $_SERVER['SERVER_NAME'];
        }

        if (!$name) {
            $name = $url;
        }

        if (false === $this->getWithoutLinks()) {
            // wenn eine dateiendung
            if (preg_match('/(\.[a-z0-9]{2,5})\/?$/i', $url)) {
                $link = '<a href="'.$url.'" target="_blank">'.$name.'</a>';
            } elseif ($hostname_url &&
                    $hostname_url != $hostname_server) {
                $link = '<a href="'.$url.'" target="_blank">'.$name.'</a>';
            } else {
                $link = '<a href="'.$url.'">'.$name.'</a>';
            }
        } else {
            $link = $name;
        }

        return $link;
    }

    public function ersetzeUrlImage($url, $image)
    {
        $link = '';
        $hostname_url = parse_url($url, PHP_URL_HOST);
        $hostname_server = 'byte-artist.de';

        if (is_array($_SERVER) &&
                array_key_exists('SERVER_NAME', $_SERVER)) {
            $hostname_server = $_SERVER['SERVER_NAME'];
        }

        if (false === $this->getWithoutLinks()) {
            // wenn eine dateiendung
            if (preg_match('/(\.[a-z0-9]{2,5})\/?$/i', $url)) {
                $link = '<a href="'.$url.'" target="_blank">'.$this->imageEinfuegen($image).'</a>';
            } elseif ($hostname_url &&
                    $hostname_url != $hostname_server) {
                $link = '<a href="'.$url.'" target="_blank">'.$this->imageEinfuegen($image).'</a>';
            } else {
                $link = '<a href="'.$url.'" target="_blank">'.$this->imageEinfuegen($image).'</a>';
            }
        } else {
            $link = $this->imageEinfuegen($image);
        }

        return $link;
    }

    // function, die checkt, ob das user ein mitglied des forums ist, wenn ja wird text angezeigt,
    // wenn nein, der register link

    private function memberText($aMatches)
    {
        $text = $aMatches[2];
        $iForumId = isset($_GET['forumid']) ? $_GET['forumid'] : '';
        $iSubForumId = isset($_GET['subforumid']) ? $_GET['subforumid'] : '';
        $iThreadId = isset($_GET['threadid']) ? $_GET['threadid'] : '';
        $iAktuelleSeite = isset($_GET['aktuelle_seite']) ? $_GET['aktuelle_seite'] : '';
        $iAnzahlPosts = isset($_GET['anzahl_posts']) ? $_GET['anzahl_posts'] : '';

        if (isset($_SESSION['mitglieder_id'])) {
            return $text;
        } else {
            return '<span style="color: red;">Bitte einloggen, oder <a style="color: red;" '.
                'href="?seite=registrieren&amp;forumid='.$iForumId.'&amp;subforumid='.$iSubForumId.'&amp;threadid='.
                $iThreadId.'&amp;aktuelle_seite='.$iAktuelleSeite.'&amp;anzahl_posts='.$iAnzahlPosts.
                '" title="Nicht die benötigten Rechte um diesen Text zu sehen" >registrieren</a>,'.
                ' damit der Text angezeigt wird !</span>';
        }
    }

    public function codeString($sLanguage, $sSource)
    {
        /*
          $code_container = '<code class="blog-code lang-' . $sLanguage . '" ><pre>' . $sSource . '</pre></code>';

          return $code_container;
         *
         */

        $sLanguage = strtoupper($sLanguage);

        $sSource = stripslashes($sSource);
        $sSource = preg_replace('/^\n/', '', $sSource);
        $sSource = preg_replace('/\n$/', '', $sSource);

        $header_content = '<div class="code_header" style="position: relative; padding: 2px 5px; font-weight: bold; '.
            'background-color: #CCCCCC; color: #333333;">';
        $header_content .= '<span class="highlight_minimize fas fa-plus" style="cursor: pointer;"></span>';

        if (strlen(trim($sLanguage)) > 0) {
            $header_content .= '<h3 style="position: absolute; top: 0px; left: 20px; padding: 2px 5px; '.
            'background-color: #FFFFFF; border: 1px solid #CCCCCC;">'.$sLanguage.' code</h3>';
        }

//     $header_content .= '<img src="#" alt="copy to clipboard" style="position: absolute; top: 2px; right: 5px;" />';
        $header_content .= '</div>';
        $footer_content = '<div class="code_footer" style="height: 10px; background-color: #CCCCCC; '.
            'color: #333333;"></div>';

        if (true === class_exists('GeSHi')) {
            $oGeshi = new GeSHi($sSource, $sLanguage);

            $oGeshi->enable_classes(true);
            $oGeshi->set_overall_class('highlight_code');
//        $oGeshi->set_header_type(GESHI_HEADER_DIV);
            $oGeshi->set_header_type(GESHI_HEADER_PRE);
            /*
             *
              GESHI_NORMAL_LINE_NUMBERS - Use normal line numbering
              GESHI_FANCY_LINE_NUMBERS - Use fancy line numbering
              GESHI_NO_LINE_NUMBERS - Disable line numbers (default)
             */
            $oGeshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);

//        $oGeshi->start_line_numbers_at($number);
//        $oGeshi->highlight_lines_extra(array(8));

            $oGeshi->set_header_content($header_content);
            $oGeshi->set_footer_content($footer_content);

            if (0 < strlen(trim($sLanguage))) {
                if (in_array(strtolower($sLanguage), $oGeshi->get_supported_languages())) {
                    $oGeshi->set_language($sLanguage);
                    if (false !== $oGeshi->get_language_name()) {
                        if ('PHP' == $sLanguage) {
                            $oGeshi->set_url_for_keyword_group(3, 'http://www.php.net/{FNAME}');
                        }
                    }

                    if (true === $this->getWithoutLinks()) {
                        $oGeshi->enable_keyword_links(false);
                    }
                } else {
                    $oGeshi->set_language('JAVASCRIPT');
                }
                $sSource = $oGeshi->parse_code();
            }
        }

        // eventuell im text enthaltene [ oder ] escapen
        $sSource = preg_replace('/\[/', '&#91;', $sSource);
        $sSource = preg_replace('/\]/', '&#93;', $sSource);

        return $sSource;
    }

    private function charEinfuegen($text)
    {
        if (preg_match('/^\#[0-9]{3,4}$/', $text)) {
            return '&'.$text.';';
        } elseif ($text > 32) {
            return chr($text);
        }
    }

    private function dankeThread($text)
    {
        if (preg_match('/\[DANKE\](.*)\[\/DANKE\]/Uis', $text)) {
            return true;
        }

        return false;
    }

    public function erstelleListe($text)
    {
        $text = stripslashes($text);

        if (preg_match('/\{ULISTE\}(.*)\{\/ULISTE\}/Usi', $text)) {
            preg_replace_callback('/\{ULISTE\}(.*)\{\/ULISTE\}/Usi', call_user_func($this, 'erstelleListe($1)'), $text);
        }

        $a_listen_punkte = preg_split('/\n|\r|\<br \/>/Ui', $text);
        $liste = '<ul style="margin-left: 10px; float: left; display: inline;">';

        foreach ($a_listen_punkte as $listen_punkt) {
            if (strlen(trim($listen_punkt)) > 0 &&
                    !preg_match('/\<ul/i', $listen_punkt)) {
                $liste .= '<li style="list-style: disc inside none; margin-left: 10px;">'.$listen_punkt.'</li>';
            } elseif (strlen(trim($listen_punkt)) > 0 && (!preg_match('/\<ul/i', $listen_punkt) ||
                    !preg_match('/\<\/ul\>/i', $listen_punkt))
            ) {
                $liste .= $listen_punkt;
            }
        }

        $liste .= '</ul>';

        return $liste;
    }

    private function imageAnhaengen($text, $name = '')
    {
        $pfad = '';
        $bild_array = [];

        // suchen, ob von extern geöffnet werden soll
        if (preg_match('/http:\/\/|http:\\\\|https:\/\/|https:\\\\|www\./i', $text)) {
            $bild_array = @getimagesize($text);
        //ansonsten aus lokalem ordner öffnen
        } else {
            if (file_exists(getcwd().$this->getBilderPfad().$text)) {
                $pfad = $this->getBilderPfad();
            }
            if (file_exists(getcwd().$this->getTempBilderPfad().$text)) {
                $pfad = $this->getTempBilderPfad();
            }
            $bild_array = getimagesize($pfad.$text);
        }

        if ($bild_array) {
            $anhang = '<div style="width: 140px; background: #FFF; border: 1px solid #666666; margin-top: 15px;">';

            if (!$name) {
                $name = basename($text);
            }

            $name_array = chunk_split($name, 20, '<br />');
            $anhang .= '<div style="display: block; padding: 5px;">'.$name_array.'</div>';
            if (false === $this->getWithoutLinks()) {
                $anhang .= '<a href="/'.$pfad.$text.'" title="'.$text.'" target="_blank" >';
            }
            $anhang .= '<img src="/butler/create-thumb/file/'.$pfad.$text.'" alt="Bild '.$text.
                ' nicht gefunden !" title="'.$text.'" />';
            if (false === $this->getWithoutLinks()) {
                $anhang .= '</a>';
            }
            $anhang .= '<div style="width: 140px; height: 20px; background: #FFF; text-align: center;">'.
                $bild_array[0].' x '.$bild_array[1].'</div>';
            $anhang .= '</div>';

            $_SESSION['post_bilder'][] = $text;

            return $anhang;
        }

        return $text;
    }

    private function imageEinfuegen($bild, $name = null)
    {
        $localHostName = null;

        if (isset($_SERVER['HTTP_HOST'])) {
            $localHostName = $_SERVER['HTTP_HOST'];
        }

        if (preg_match('/http\:\/\/|http\:\\\\|https\:\/\/|https\:\\\\|www\./Ui', $bild)) {
            $a_bildinformationen = @getimagesize($bild);
        } elseif (file_exists(getcwd().$this->getTempBilderPfad().$bild) &&
                is_file(getcwd().$this->getTempBilderPfad().$bild) &&
                is_readable(getcwd().$this->getTempBilderPfad().$bild)
        ) {
            $bild = 'https://'.$localHostName.$this->getTempBilderPfad().$bild;
        } elseif (file_exists(getcwd().$this->getBilderPfad().$bild) &&
                is_file(getcwd().$this->getBilderPfad().$bild) &&
                is_readable(getcwd().$this->getBilderPfad().$bild)
        ) {
            $bild = 'https://'.$localHostName.$this->getBilderPfad().$bild;
        }

        $bild_link = '';

        if ($name) {
            $name = addslashes($name);
            $bild_link .= '<p style="clear: both; float: left; display: inline; padding: 5px 0 2px 0; '.
                'margin: 5px 0 0 0;">'.$name.'</p>';
        }
        $bild_link .= '<img style="float: left; display: inline;" src="'.$bild.'" alt="Bild '.$bild.
            ' nicht gefunden !" title="'.$bild.'" />';

        return $bild_link;
    }

    private function imageEinfuegenNeu($imagePathName, $params, $generateWithContainer = true)
    {
        $name = '';

        $imagePathName = $this->ersetzeUmlaute($imagePathName);
        $imageContent = $this->generateImageContent($imagePathName, $params);

        $returnContent = '';

        if (true === $generateWithContainer) {
            $returnContent = '<div class="blog_pic_container" >';
        }

        $a_params = explode(':', $params);

        foreach ($a_params as $a_param) {
            $a_style = explode('=', $a_param);
            if (1 === count($a_style)
                && 0 == strlen($name)
            ) {
                $name = addslashes($a_style[0]);
                break;
            } elseif ('name' == strtolower($a_style[0])) {
                $name = addslashes($a_style[1]);
                break;
            }
        }
        $name = $imagePathName;
//        $returnContent .= '<img class="blog_pic lazyload" data-src="'.$imageContent.'" src="load.jpg" alt="Bild '
//           . $name . ' nicht gefunden !" title="' . $name . '" />';
        $returnContent .= '<img class="blog_pic lazyload" data-mfp-src="'.$imageContent.'" data-src="'.$imageContent.
            '" src="low_pic.png" alt="Bild '.$name.' nicht gefunden !" title="'.$name.'" />';
        /*
                if ($name
                    && true === $generateWithContainer
                ) {
                    $returnContent .= '<p>' . $name . '</p>';
                }
        */
        if (true === $generateWithContainer) {
            $returnContent .= '</div>';
        }

        return $returnContent;
    }

    private function generateImageContent($imagePathName, $params = '')
    {
        $imagePathName = $this->ersetzeUmlaute($imagePathName);

        $imagePathNameFormatted = $imagePathName;
        $localHostName = null;

        if (isset($_SERVER['HTTP_HOST'])) {
            $localHostName = $_SERVER['HTTP_HOST'];
        }

        if (preg_match('/http\:\/\/|http\:\\\\|https\:\/\/|https\:\\\\|www\./Ui', $imagePathName)) {
            $this->setTempBilderPfad(getcwd().'/tmp/butler/');
            $obj_cad_file = new File();
            if ($obj_cad_file->checkAndCreateDir($this->getTempBilderPfad())
                && true === is_readable($imagePathName)
            ) {
                $imagePathNameFormatted = $this->getTempBilderPfad().'dummy.jpg';
                file_put_contents($imagePathNameFormatted, file_get_contents($imagePathName));
                $imagePathNameFormatted = '/butler/create-thumb/file/'.base64_encode($imagePathNameFormatted);
            }
        } elseif (file_exists(getcwd().$this->getTempBilderPfad().$imagePathName) &&
                is_file(getcwd().$this->getTempBilderPfad().$imagePathName) &&
                is_readable(getcwd().$this->getTempBilderPfad().$imagePathName)
        ) {
            $imagePathNameFormatted = 'https://'.$localHostName.'/butler/create-thumb/file/'.
                base64_encode(getcwd().$this->getTempBilderPfad().$imagePathNameFormatted);
        } elseif (file_exists(getcwd().$this->getBilderPfad().$imagePathName)
            && is_file(getcwd().$this->getBilderPfad().$imagePathName)
            && is_readable(getcwd().$this->getBilderPfad().$imagePathName)
        ) {
//            $imagePathNameFormatted = 'https://'.$localHostName.'/butler/create-thumb/file/'.
//              base64_encode(getcwd().$this->getBilderPfad().$imagePathNameFormatted);
            $imagePathNameFormatted = 'http://'.$localHostName.$this->getBilderPfad().$imagePathNameFormatted;
        } else {
            $imagePathNameFormatted = 'https://'.$localHostName.$this->getBilderPfad().$imagePathName;
        }

        $a_params = explode(':', $params);

        foreach ($a_params as $a_param) {
            $a_style = explode('=', $a_param);
            if (1 < count($a_style) && 'name' != strtolower($a_style[0]) && isset($a_style[0]) && isset($a_style[1])
            ) {
                $imagePathNameFormatted .= '/'.$a_style[0].'/'.$a_style[1];
            }
        }

        return $imagePathNameFormatted;
    }

    public function setBilderPfad($str_pfad)
    {
        $this->str_bilder_pfad = $str_pfad;
    }

    public function getBilderPfad()
    {
        return $this->str_bilder_pfad;
    }

    public function setTempBilderPfad($str_pfad)
    {
        $this->str_temp_bilder_pfad = $str_pfad;
    }

    public function getTempBilderPfad()
    {
        return $this->str_temp_bilder_pfad;
    }

    private function sonderzeichenErsetzen($text)
    {
        $replace = [
            '/ä/' => '&auml;',
            '/Ä/' => '&Auml;',
            '/ü/' => '&uuml;',
            '/Ü/' => '&Uuml;',
            '/ö/' => '&ouml;',
            '/Ö/' => '&Ouml;',
            '/ß/' => '&szlig;',
        ];

        $text = preg_replace(array_keys($replace), array_values($replace), $text);

        return $text;
    }

    private function erstelleListenpunkt($text)
    {
        $text = '<li style="margin-left: 20px;">'.$text.'</li>';

        return $text;
    }

    private function erstelleListenueberschrift($text)
    {
        $text = '<span style="margin-left: 16px;">'.$text.'</span>';

        return $text;
    }

    private function ersetzeUmlaute($text)
    {
        $text = str_replace(
            ['ä', 'ö', 'ü', 'ß', 'Ä', 'Ö', 'Ü'],
            ['ae', 'oe', 'ue', 'ss', 'Ae', 'Oe', 'Ue'],
            $text
        );

        $text = str_replace(
            ["\xC4", "\xD6", "\xDC", "\xDF", "\xE4", "\xF6", "\xFC"],
            ['Ae', 'Oe', 'Ue', 'ss', 'ae', 'oe', 'ue'],
            $text
        );

        return $text;
    }

    private function detectUTF8($string)
    {
        return (bool) preg_match('%(?:
            [\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
            |\xE0[\xA0-\xBF][\x80-\xBF]               # excluding overlongs
            |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
            |\xED[\x80-\x9F][\x80-\xBF]               # excluding surrogates
            |\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
            |[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
            |\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
            )+%xs', $string);
    }

    private function smileyReplace($sText)
    {
        $this->prepareSmileys();

        $sText = preg_replace(array_keys(self::$replaceSmilies), array_values(self::$replaceSmilies), $sText);

        return $sText;
    }

    private function prepareSmileys()
    {
        $sTheme = 'standart';

        if (true === isset($_SESSION['smiley_theme'])) {
            $sTheme = $_SESSION['smiley_theme'];
        }

        if (null === self::$replaceSmilies) {
            self::$replaceSmilies = [];

            $oSmileysDbTable = new Cms_Model_DbTable_Smileys();
            $oSmileysRowSet = $oSmileysDbTable->findSmileys($sTheme, $sCategory = null);

            foreach ($oSmileysRowSet as $oSmileyRow) {
                $sRegEx = '/'.$this->escapeRegEx($oSmileyRow->smile_short).'/i';

                $sPicturePath = '<img src="/images/content/statisch/grafiken/smileys/'.$sTheme.'/'.
                        $oSmileyRow->smile_picture.'" alt="'.$oSmileyRow->smile_short.'" title="'.
                        $oSmileyRow->smile_short.'" />';

                self::$replaceSmilies[$sRegEx] = $sPicturePath;
            }
        }
    }

    private function escapeRegEx($sText)
    {
        return preg_replace('/([\-|\.|\(|\)|\/|\?|\\\])/', '\\\$1', $sText);
    }

    public function dankeText($sText)
    {
        return 'Danke, '.$sText.'!';
    }

    public function getNews($aMatches)
    {
        return __METHOD__.'NEWS';
    }

    /**
     * @param bool $bWithoutLinks
     */
    public function setWithoutLinks($bWithoutLinks)
    {
        $this->bWithoutLinks = $bWithoutLinks;

        return $this;
    }

    /**
     * @return bool
     */
    public function getWithoutLinks()
    {
        return $this->bWithoutLinks;
    }

    /**
     * @return array
     */
    public function getAllowdTags()
    {
        return $this->allowdTags;
    }

    /**
     * @param array $aAllowdTags
     */
    public function setAllowdTags($aAllowdTags)
    {
        $this->allowdTags = $aAllowdTags;
    }

    /**
     * @return array
     */
    public function getMapTagToFunction()
    {
        return $this->aMapTagToFunction;
    }

    /**
     * @param array $aMapTagToFunction
     */
    public function setMapTagToFunction($aMapTagToFunction)
    {
        $this->aMapTagToFunction = $aMapTagToFunction;
    }

    /**
     * @return bool
     */
    public function isRemoveDeniedTags()
    {
        return $this->removeDeniedTags;
    }

    /**
     * @param bool $bRemoveDeniedTags
     */
    public function setRemoveDeniedTags($bRemoveDeniedTags)
    {
        $this->removeDeniedTags = $bRemoveDeniedTags;
    }

    /**
     * @return bool
     */
    public function isSolveVideoInformation()
    {
        return $this->solveSourceInformation;
    }

    /**
     * @param bool $solveVideoInformation
     */
    public function setSolveVideoInformation($solveVideoInformation)
    {
        $this->solveSourceInformation = $solveVideoInformation;
    }
}
