# Boilerplate API

## Requisitos mínimos
- PHP 7.1+
- MySQL 5.6+ (5.7)
- Composer


## Instalação


Instale as dependências:
```bash
composer install
```

Criar arquivo `local.php` em `/config/autoload` seguindo a estrura abaixo:
```php
<?php
error_reporting(E_ALL & ~ E_DEPRECATED & ~ E_USER_DEPRECATED  & ~ E_STRICT);

return array(
    'db' => array(
        'username' => 'root',
        'password' => ''
    ),
    'eloquent' => [
        'driver'    => 'mysql',
        'host'      => '127.0.0.1',
        'database'  => 'skeleton',
        'username'  => 'root',
        'password'  => 'root',
        'charset'   => 'utf8',
        'collation' => 'utf8_general_ci',
    ],
    'environment' => 'DEVELOPMENT'
);
```
**Sugiro fortemente não utilizar o usuário "root" para conexões ao mysql. Crie um usuário exclusivo para sua aplicação e restrinja o acesso dele apenas ao database da aplicação.**


---

### Phinx: gerenciando migrations e seeders

O versionamento da base de dados do projeto é feito com o phinx. Ele é instalado no composer. Para gerenciar, siga os passos:
* Criar uma base de dados com o nome desejado;
* Depois executar: 
```bash
php vendor/bin/phinx init
```

Alterar o arquivo phinx.yml:
- substituir  `/db/` por `/database/` do caminho dos arquivos de migração; (linhas 2 e 3)
- alterar o bloco `development` conforme seu ambiente; (por default:localhost/root/sem senha)
- alterar o campo `default_database` para `skeleton`;

Para criar a base executar:

```bash
php vendor/bin/phinx migrate -e development
```

Para popular com dados iniciais a base:

```bash
php vendor/bin/phinx seed:run -e development
```

### PHP: extensões necessárias 

- Intl

## Como iniciar?

Basta abrir um terminal, posicionar até a raiz do projeto (dentro da pasta do projeto) e executar o seguinte comando:
```bash
php -S localhost:<PORTA> public/index.php
```

## Pontos a serem melhorados

Alguns itens já identificados e que podem ser melhorados em uma próxima versão para otimizar código:
- Uso de Validators nos métodos de verbo (get, post, put, del), reduzindo a complexidade e limpando o código;
