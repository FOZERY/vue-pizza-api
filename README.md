# Set ENV variables in config.php

```php
$_ENV["JWT_KEY"] = "";
$_ENV["JWT_ISS"] = "";
$_ENV["JWT_AUD"] = "";
$_ENV["JWT_IAT"] = "";
$_ENV["JWT_NBF"] = "";

$_ENV["EXOLVE_API_URL"] = "";
$_ENV["EXOLVE_API_NUMBER"] = "";
$_ENV["EXOLVE_API_KEY"] = "";

$_ENV["DB_HOST"] = "";
$_ENV["DB_PORT"] = "";
$_ENV["DB_NAME"] = "";
$_ENV["DB_USERNAME"] = "";
$_ENV["DB_PASSWORD"] = "";

$_ENV["ROOT_PATH"] = realpath($_SERVER["DOCUMENT_ROOT"]);
```