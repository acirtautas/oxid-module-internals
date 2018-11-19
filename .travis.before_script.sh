# OXID Installieren
cd ~/
mkdir OXID
cd OXID
composer create-project oxid-esales/oxideshop-project . dev-b-6.0-ce
sed -i -e "s@<dbHost>@127.0.0.1@g" source/config.inc.php
sed -i -e "s@<dbName>@oxid@g" source/config.inc.php
sed -i -e "s@<dbUser>@root@g" source/config.inc.php
sed -i -e "s@<dbPwd>@@g" source/config.inc.php
sed -i -e "s@<sShopURL>@http://127.0.0.1@g" source/config.inc.php
sed -i -e "s@<sShopDir>@/home/travis/OXID/source@g" source/config.inc.php
sed -i -e "s@<sCompileDir>@/home/travis/OXID/source/tmp@g" source/config.inc.php
sed -i -e "s@partial_module_paths: null@partial_module_paths: fp/debugbar@g" test_config.yml
sed -i -e "s@run_tests_for_shop: true@run_tests_for_shop: false@g" test_config.yml

# DebugBar Registrieren
composer config repositories.oxid-community/moduleinternals vcs ${TRAVIS_BUILD_DIR}
composer require "oxid-community/moduleinternals:dev-${BRANCH}#${TRAVIS_COMMIT}"
