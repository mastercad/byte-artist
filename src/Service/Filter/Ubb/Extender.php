<?php

namespace App\Service\Filter\Ubb;

use App\Service\DOM\TagMerge;

/**
 * Class extends given Information in ubb tags with static content, for example name of an YouTube Video or
 * name of user for saved text.
 */
class Extender
{
    /**
     * @var string
     */
    public const REGEX = 'regex';

    /**
     * @var string
     */
    public const REPLACE_FUNCTION = 'replaceFunction';

    /**
     * @var array contains all allowed tags for replacement
     */
    private array $aAllowedTags = [
        '[VIDEO]' => ['[VIDEO]', '[/VIDEO]'],
    ];

    /**
     * @var array map of tag and regex
     */
    private array $aMapTagToFunction = [
        '[VIDEO]' => [
            self::REGEX => '/(\[(VIDEO:|VIDEO=)([^\]]*)\]|\[VIDEO\])([^(\[\\)]*)\[\/(VIDEO)\]/i',
            self::REPLACE_FUNCTION => 'addVideoInformation',
        ],
    ];

    /**
     * CTOR.
     */
    public function __construct()
    {
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        error_reporting(E_ALL | E_STRICT);
    }

    /**
     * Main entry function to replace given context.
     *
     * @var string
     *
     * @return string
     */
    public function filter($sText)
    {
        foreach ($this->aAllowedTags as $sAllowedTag => $params) {
            if (array_key_exists($sAllowedTag, $this->aMapTagToFunction)) {
                $aCurrentMap = $this->aMapTagToFunction[$sAllowedTag];

                $sText = preg_replace_callback(
                    $aCurrentMap[self::REGEX],
                    [&$this, $aCurrentMap[self::REPLACE_FUNCTION]],
                    $sText
                );
            }
        }

        $oCadMerge = new TagMerge();

        return $oCadMerge->merge($sText);
    }

    /**
     * Group1 : Video Tag and possible Options
     * Group2 : Video Tag and Separator (:|= or nothing)
     * Group3 : possible Options
     * Group4 : VideoURL
     * Group5 : Video End Tag.
     *
     * @return string
     */
    private function addVideoInformation($matches)
    {
        if (true === is_array($matches)
            && array_key_exists(4, $matches)
            && (preg_match('/^http[s]{0,1}:\/\/www\.youtu.*/i', $matches[4])
                || preg_match('/^http[s]{0,1}:\/\/youtu.*/i', $matches[4])
            )
        ) {
            $tagName = $matches[5].':';
            $name = $this->retrieveYouTubeVideoInformation($this->parseYouTubeVideoUrl($matches[4]));
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

            return '['.$tagName.$optionsString.']'.$matches[4].'[/'.$matches[5].']';
        }

        return $matches[0];
    }

    /**
     * @return false|string
     */
    private function generateOptionsString($options)
    {
        $optionsString = '';
        foreach ($options as $key => $value) {
            $optionsString .= $key.'='.$value.'|';
        }
        $optionsString = substr($optionsString, 0, -1);

        return $optionsString;
    }

    private function retrieveYouTubeVideoInformation($videoId): string
    {
        $content = str_replace('|', ' ', $this->retrieveYouTubeTitle($videoId));
        $content = str_replace('=', ' ', $content);

        return $content;
    }

    private function parseYouTubeVideoUrl($videoUrl): string
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

    private function retrieveYouTubeTitle($videoId)
    {
        $videoInformation = $this->retrieveYouTubeInformation($videoId);

        return $videoInformation['items'][0]['snippet']['title'];
    }

    private function retrieveYouTubeInformation($videoId)
    {
        $jsonString = file_get_contents('https://www.googleapis.com/youtube/v3/videos?id='.$videoId.
                '&key='.$_ENV['GOOGLE_API_KEY'].'&part=snippet,contentDetails,statistics,status');

        return json_decode($jsonString, true);
    }

    /**
     * @return string[]
     *
     * @psalm-return array<string, string>
     */
    private function parseOptions($optionsString): array
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
