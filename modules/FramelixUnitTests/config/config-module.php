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
        }
    }
</script>
