<?php namespace CeesVanEgmond\Minify\Providers;

use CeesVanEgmond\Minify\Contracts\MinifyInterface;
use CssMinifier;

class StyleSheet extends BaseProvider implements MinifyInterface
{
    /**
     *  The extension of the outputted file.
     */
    const EXTENSION = '.css';

    /**
     * @return string
     */
    public function minify()
    {
        $minified = new CssMinifier($this->appended);

        return $this->put($minified->getMinified());
    }


    /**
     *
     */
    protected function appendFiles()
    {
        foreach ($this->files as $sFile) {

            $sToAppend = file_get_contents($sFile);

            // Find url attributes with relative paths and change them to the new public location
            $aMatches = array();
            $aUrls = $this->findUrlAttributes($sToAppend, $aMatches);

            if (isset($aUrls) && is_array($aUrls)) {
                $sToAppend = $this->modifyUrlAttributes($sFile, $sToAppend, $aUrls);
            }

            $this->appended .= $sToAppend;
        }

    }


    /**
     *
     * Searchs for url-attributes with relative paths
     *
     * @param $subject
     * @param $matches
     * @return bool
     */
    private function findUrlAttributes($subject, &$matches) {

        if (!isset($matches)) {
            return null;
        }

        $bFound = preg_match_all('*url\((\\\'|\\")([^\/][\w\s\d-/_.?#]+)(\\\'|\\")\)*',$subject, $matches) > 0;
        if ($bFound && isset($matches[2]) && count($matches[2]) > 0) {
            return $matches[2];
        }
        return null;
    }


    /**
     *
     *
     * @param $file
     * @param $sToAppend
     * @param $urlsToModify
     * @return mixed
     */
    private function modifyUrlAttributes($file, $sToAppend, $urlsToModify) {
        $sOutputDirDiff = str_replace($this->publicPath.'/','',$this->outputDir);
        $sPathFromPublic = str_replace($this->publicPath.'/','',dirname($file)).'/';
        $iGoDirBackCount = substr_count($sOutputDirDiff,'/');



        foreach ($urlsToModify as $value) {

            return str_replace($value, str_repeat('../',$iGoDirBackCount).$sPathFromPublic.$value, $sToAppend);

        }
    }


    /**
     * @param $file
     * @param array $attributes
     * @return string
     */
    public function tag($file, array $attributes = array())
    {
        $attributes = array('href' => $file, 'rel' => 'stylesheet') + $attributes;

        return "<link {$this->attributes($attributes)}>" . PHP_EOL;
    }
}
