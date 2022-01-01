<?php

// prevent loading directly in the browser without framelix context
if (!defined("FRAMELIX_MODULE")) {
    die();
}
// this config represents the module configuration defaults
// this are settings that are defined by the module developer
// some keys may be editable in the configuration admin interface
// which then will be saved into config-editable.php
?>
<script type="application/json">
    {
        "applicationHttps": false,
        "applicationHost": "localhost",
        "applicationUrlBasePath": "\/",
        "salts": {
            "general": "jdTbhul2sd3yyaLQPfTFNToE42PcXOCC991SzzKlUrQhS1hhkdTIHufuJ8Sj6XPgd"
        },
        "compiler": {
            "FramelixTests": {
                "js": {
                    "test-folder": {
                        "files": [
                            {
                                "type": "folder",
                                "path": "js",
                                "recursive": true
                            }
                        ]
                    },
                    "test-path": {
                        "files": [
                            {
                                "type": "file",
                                "path": "js/framelix-unit-test-jstest.js",
                                "recursive": true
                            }
                        ]
                    },
                    "test-path-array": {
                        "files": [
                            {
                                "type": "file",
                                "path": [
                                    "js/framelix-unit-test-jstest.js"
                                ]
                            }
                        ]
                    },
                    "test-nocompile": {
                        "files": [
                            {
                                "type": "file",
                                "path": [
                                    "js/framelix-unit-test-jstest.js"
                                ]
                            }
                        ],
                        "options": {
                            "noCompile": true
                        }
                    },
                    "test-nocompile-ignorefile": {
                        "files": [
                            {
                                "type": "folder",
                                "path": "js",
                                "ignoreFilenames": [
                                    "framelix-unit-test-jstest2.js"
                                ]
                            }
                        ],
                        "options": {
                            "noCompile": true
                        }
                    },
                    "test-empty": {
                        "files": [

                        ]
                    }
                },
                "scss": {
                    "test-folder": {
                        "files": [
                            {
                                "type": "folder",
                                "path": "scss",
                                "recursive": true
                            }
                        ]
                    },
                    "test-path": {
                        "files": [
                            {
                                "type": "file",
                                "path": "scss/framelix-unit-test-scsstest.scss"
                            }
                        ]
                    },
                    "test-path-array": {
                        "files": [
                            {
                                "type": "file",
                                "path": [
                                    "scss/framelix-unit-test-scsstest.scss"
                                ]
                            }
                        ]
                    }
                }
            }
        }
    }
</script>