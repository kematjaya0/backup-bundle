# backup-bundle for symfony 5
- base on https://github.com/spatie/db-dumper

1. installation
```
composer require kematjaya/backup-bundle
```
2. setting
```
## config/packages/backup.yaml

backup:
    name: postgresql
    location: '%kernel.project_dir%/var/backup'
```
3. usage
```
php bin/console database:dump
```