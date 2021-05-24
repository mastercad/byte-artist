<?php

namespace App\Service\Filter\Ubb;

use App\Service\DOM\TagMerge;

/**
 * Klasse erweitert übergebene informationen in ubb tags um statische inhalte, wie z.b. die namen von YT Videos
 * zum tag der speicherung oder die namen von mitgliedern beim speichern des textes
 */
class Extender
{
    /**
     * string
     */
    const REGEX = 'regex';

    /**
     * string
     */
    const REPLACE_FUNCTION = 'replace_function';

    /**
     * @var array enhält alle erlaubten tags, die replaced werden sollen
     */
    protected $aAllowdTags = array(
        '[VIDEO]' => ['[VIDEO]', '[/VIDEO]'],
    );

    /**
     * @var array enthält das Tag und dazu den regex sowie den funktionsaufruf
     */
    protected $aMapTagToFunction = array(
        '[VIDEO]' => array(
//            self::REGEX => '/(\[VIDEO\])(.*?)(\[\/VIDEO\])/i',
            self::REGEX => '/(\[(VIDEO:|VIDEO=)([^\]]*)\]|\[VIDEO\])([^(\[\\)]*)\[\/(VIDEO)\]/i',
            self::REPLACE_FUNCTION => 'addVideoInformation'
        ),
    );

    public function __construct()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL | E_STRICT);
    }

    /**
     * @var string $sText
     *
     * @return $string
     */
    public function filter($sText)
    {
        foreach ($this->aAllowdTags as $sAllowedTag => $params) {
            if (array_key_exists($sAllowedTag, $this->aMapTagToFunction)) {
                $aCurrentMap = $this->aMapTagToFunction[$sAllowedTag];

                $sText = preg_replace_callback(
                    $aCurrentMap[self::REGEX],
                    array(&$this, $aCurrentMap[self::REPLACE_FUNCTION]),
                    $sText
                );
            }
        }

        $oCadMerge = new TagMerge();

        return $oCadMerge->merge($sText);
    }

    /**
     *
     * Group1 : Video Tag and possible Options
     * Group2 : Video Tag and Separator (:|= or nothing)
     * Group3 : possible Options
     * Group4 : VideoURL
     * Group5 : Video End Tag
     *
     * @return string
     */
    public function addVideoInformation($matches)
    {
        if (true === is_array($matches) && array_key_exists(4, $matches)
        ) {
            if (preg_match('/^http[s]{0,1}:\/\/www\.youtu.*/i', $matches[4])
                || preg_match('/^http[s]{0,1}:\/\/youtu.*/i', $matches[4])
            ) {
                $tagName = $matches[5] . ':';
                $name = $this->retrieveYoutubeVideoInformation($this->parseYoutubeVideoUrl($matches[4]));
                $options = [];
                // options already exists
                if (array_key_exists(2, $matches)
                    && 0 < strlen(trim($matches[2]))
                    && array_key_exists(3, $matches)
                    && 0 < strlen(trim($matches[3]))
                ) {
                    $tagName = $matches[2];
                    $options = $this->parseOptions($matches[3]);
                }
                $options['name'] = $name;
                $optionsString = $this->generateOptionsString($options);

                return '[' . $tagName . $optionsString . ']' . $matches[4] . '[/' . $matches[5] . ']';
            }
        };
        return $matches[0];
    }

    private function generateOptionsString($options)
    {
        $optionsString = '';
        foreach ($options as $key => $value) {
            $optionsString .= $key . '=' . $value . '|';
        }
        $optionsString = substr($optionsString, 0, -1);
        return $optionsString;
    }

    /**
     *
     */
    private function retrieveYoutubeVideoInformation($videoId)
    {
        $content = str_replace('|', ' ', $this->retrieveYoutubeTitle($videoId));
        $content = str_replace('=', ' ', $content);

        return $content;
    }

    /**
     *
     */
    private function parseYoutubeVideoUrl($videoUrl)
    {
        $sVideoId = $videoUrl;

        if (preg_match('/youtu\.be\/(.*)/i', $videoUrl, $aMatches)
            || preg_match('/youtube.*[\?|\&]v=([\-\_A-Za-z0-9]{1,})/i', $videoUrl, $aMatches)
            || preg_match('/youtube.*?v=(.*?)/i', $videoUrl, $aMatches)
        ) {
            $sVideoId = $aMatches[1];
        }
        return trim($sVideoId);
    }

    private function retrieveYoutubeTitle($videoId)
    {
        $videoInformation = $this->retrieveYoutubeInformation($videoId);

        return $videoInformation['items'][0]['snippet']['title'];
    }

    private function retrieveYoutubeInformation($videoId)
    {
        $jsonString = file_get_contents("https://www.googleapis.com/youtube/v3/videos?id=" . $videoId .
                "&key=" . GOOGLE_API_KEY . "&part=snippet,contentDetails,statistics,status");

        $json = json_decode($jsonString, true);

        return $json;
    }

//    private function parseOptions($optionsString) {
//        $options = explode('|', $optionsString);
//
//        return $options;
//    }

    private function parseOptions($optionsString)
    {
        $rawOptions = explode('|', $optionsString);
        $options = [];

        foreach ($rawOptions as $option) {
            $optionData = explode('=', $option);
            $options[$optionData[0]] = $optionData[1];
        }

        return $options;
    }
}
