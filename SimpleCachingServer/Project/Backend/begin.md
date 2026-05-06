File structure::

simple-cache-server/

├── bin/

│   ├── start.sh // 

│   ├── stop.sh

│   └── restart.sh

├── config/

│   ├── bootstrap.json

│   └── auth.php

├── logs/

│   └── cache-server.log

├── run/

│   └── cache-server.pid

├── public/

│   └── router.php

├── src/

│   ├── App/

│   │   └── Application.php

│   ├── Bootstrap/

│   │   └── BootstrapLoader.php

│   ├── Cache/

│   │   ├── CacheItem.php

│   │   └── CacheService.php

│   ├── Controller/

│   │   ├── CacheController.php

│   │   └── AdminCacheController.php

│   ├── Auth/

│   │   ├── AuthService.php

│   │   └── Role.php

│   ├── Logging/

│   │   └── Logger.php

│   └── Http/

│       ├── Request.php

│       ├── JsonResponse.php

│       └── ResponseFactory.php

├── server.php

├── composer.json

└── README.md



//so now starting with independent files
1. Role.php- done - Stores role names like normal and admin in one place.

2. JsonResponse.php- done - Sends every API response as JSON with correct HTTP status code

3. ResponseFactory.php - Creates std success/error response formats so all api look same!

4. Logger.php - Writes startup,error,auth,and cache op logs to file.