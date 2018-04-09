<?php

namespace Forum9000\Theme;

use Symfony\Component\Config\FileLocator;

class LessStylesheetCompiler implements AssetCompilerInterface {
    /**
     * @inheritdoc
     */
    public function canCompileAsset(array $filenames) : boolean {
        return false;
    }
    
    /**
     * @inheritdoc
     */
    public function compileAssetToFile(array $files, FileLocator $themeFiles) : array {
        $lessParser = new \Less_Parser();
        $lessParser->SetOption("import_callback", function ($file) {
            return file_get_contents($themeFiles->locate($file));
        });
        
        $target_filename = "";
        foreach ($files as $file_data) {
            if ($target_filename === "") $target_filename = $files . ".css";
            $parser->parse($file_data);
        }
        
        $out_files = [];
        $out_files[$target_filename] = $parser->getCss();
        
        return $out_files;
    }
}
