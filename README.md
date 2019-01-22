# TODO List API

Uma API simples para gerenciamento de tarefas organizadas por usuários.

Esta API foi criada para atender a um teste de desenvolvimento. Ela foi estendida de um projeto Boilerplate que criei, disponível em https://github.com/andrepiacentini/boilerplate-api
Todos os arquivos desnecessários foram removidos, porém algumas classes podem conter métodos que não necessariamente são utilizados para este projeto exemplo.

Uma outra API desenvolvida em Lumen + Dingo poderia ser utilizada mas optei em não utilizar pois encapsula muitas regras que gostaria de mostrar (como o Authenticate com JWT), 

## Requisitos mínimos
- PHP 7.1+
- MySQL 5.6+
- Composer (https://getcomposer.org)


## Instalação


Instale as dependências:
```bash
composer install
```

Libere acesso a escrita a pasta `data` para o usuário do seu servidor web ou do proprietário do script PHP

Crie o arquivo `local.php` em `/config/autoload` com as configurações do seu ambiente, seguindo a estrura abaixo:
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
- alterar o bloco `development` conforme seu ambiente;

Para criar o banco, basta executar a partir da raiz do projeto:

```bash
php vendor/bin/phinx migrate -e development
```

Para popular com dados iniciais o banco, execute a partir da raiz do projeto:

```bash
php vendor/bin/phinx seed:run -e development
```


## Como iniciar?

Basta abrir o terminal, posicionar até a raiz do projeto e executar o seguinte comando:
```bash
php -S localhost:<PORTA> public/index.php
```

## Onde estão os endpoints?

Utilize o Postman para acessar os endpoints desta API (https://getpostman.com)

Após sua instalação, importe a coleção (`/data/collection.json`) e o ambiente (`/data/environment.json`)

Comece pelo request `Autenticar` da coleção. Em seguida crie sua primeira Todo List usando a request `Lists > Criar Lista`

Criada a primeira lista, use o seu ID para passar como parâmetro no endpoint `Tasks > Criar Tarefa`


## O que precisa melhorar?

* Documentação mais amigável usando Swagger;
* Interface para uso da API (novo projeto em breve)  