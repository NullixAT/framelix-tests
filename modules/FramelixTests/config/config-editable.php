<?php

// prevent loading directly in the browser without framelix context
if (!defined("FRAMELIX_MODULE")) {
    die();
}
// this config represents the editable configuration that can be changed by the user in the admin interface
// this should not be under version control
?>
<script type="application/json">
    {
        "database": {
            "test": {
                "host": "db",
                "username": "app",
                "password": "app",
                "database" : "app",
                "port": 3306,
                "socket": ""
            }
        }
    }
</script>
