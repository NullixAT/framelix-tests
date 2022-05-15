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
                "host": "127.0.0.1",
                "username": "admin",
                "password": "rootpass",
                "database": "test_database",
                "port": 3306,
                "socket": ""
            }
        }
    }
</script>
