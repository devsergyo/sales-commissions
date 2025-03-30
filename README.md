# Sistema de Comissões de Vendas - Instruções de Instalação

## Requisitos

- Docker e Docker Compose
- WSL2 (para Windows) ou ambiente Linux/MacOS
- Git

## Passo a Passo para Instalação

### 1. Clone o Repositório

```bash
git clone https://github.com/devsergyo/sales-commissions.git
cd sales-commissions
```
#### Copiar o arquivo .env.example para .env

```bash
cp .env.example .env
```
#### Configurar o usuário padrão
    - No seu arquivo .env há dois parâmetros que deverão ser configurados para que utilize o painel administrativo:
      - USER_NAME_ROOT=(deverá ser o nome completo do admin)
      - USER_MAIL_ROOT=(deverá ser o e-mail válido do admin) 
      - USER_PASSWORD_ROOT=(deverá ser uma senha forte)

    - Caso nenhum seja informado irá ser o padrão
        Nome: Joscrino Testador
        E-mail: teste@testcompany.com.br
        Senha: password

#### Configurar o Banco de Dados

O arquivo .env já vem configurado para se conectar ao container MySQL, mas se necessário, ajuste as seguintes variáveis:

```
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=app_db
DB_USERNAME=user
DB_PASSWORD=password
```

### 2. Configuração do Ambiente

#### Construir e Iniciar os Containers

```bash
docker-compose build
docker-compose up -d
```

### 3. Configuração do Backend (Laravel)

#### Instalar Dependências e Configurar o .env

```bash
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
```

### 4. Executar testes do backend:

```bash
docker-compose exec app php artisan test --testsuite=Feature
```


Este comando irá criar e iniciar os seguintes containers:
- `php_app`: Backend Laravel (PHP 8.4)
- `mysql_db`: Banco de dados MySQL
- `redis_cache`: Redis para cache
- `nginx_server`: Servidor web Nginx
- `vue_frontend`: Frontend Vue.js

#### Executar as Migrations

```bash
docker-compose exec app php artisan migrate
```

#### Populando banco de dados:

Você poderá utilizar o comando abaixo para popular o banco de dados com dados de exemplo:

```bash
docker-compose exec app php artisan db:seed
```

Ou caso queira apenas os dados de usuário do sistema:

```bash
docker-compose exec app php artisan db:seed --class=UserSeeder
```


### 4. Configuração do Frontend (Vue.js)

#### Instalar Dependências

```bash
docker-compose exec frontend npm install
```

#### Compilar e Iniciar o Servidor de Desenvolvimento

```bash
docker-compose exec frontend npm run dev
```

### 5. Acessar a Aplicação

- **Frontend**: http://localhost
- **API Backend**: http://localhost/api

## Configurações Adicionais

### CORS e Sanctum

A aplicação já está configurada para permitir requisições do frontend para o backend através da API Laravel Sanctum. As configurações estão nos arquivos:

- `config/cors.php`: Configurações de CORS
- `config/sanctum.php`: Configurações de autenticação Sanctum

### Atualizações no Código

Ao fazer alterações no código, dependendo do tipo de mudança, você pode precisar:

1. **Backend**: Reiniciar o container Laravel
   ```bash
   docker-compose restart app
   ```

2. **Frontend**: O Vite já atualiza automaticamente as mudanças em modo de desenvolvimento.

## Solução de Problemas

### Problemas de Permissão

Se encontrar problemas de permissão, execute:

```bash
docker-compose exec app chmod -R 777 storage bootstrap/cache
```

### Logs

Verifique os logs para debugar problemas:

```bash
docker-compose logs -f
```

Ou para um container específico:

```bash
docker-compose logs -f app
```

## Comandos Úteis

### Artisan

```bash
docker-compose exec app php artisan [comando]
```

### NPM

```bash
docker-compose exec frontend npm [comando]
```

### Composer

```bash
docker-compose exec app composer [comando]
```

### MySQL

```bash
docker-compose exec db mysql -u user -p
```


## Processamento de Emails

Para iniciar o processamento de emails em fila, execute:

```bash
docker-compose exec app php artisan queue:process-emails
```

Este comando irá:
- Processar todos os emails pendentes na fila
- Enviar relatórios de comissões para os vendedores
- Exibir o status do processamento no console

Para manter o processamento de emails rodando continuamente em background, você pode usar:

```bash
docker-compose exec -d app php artisan queue:process-emails
```

## Estrutura do Projeto

- `/`: Arquivos principais do projeto
- `/app`: Código do backend Laravel
- `/resources/js`: Código do frontend Vue.js
- `/docker`: Arquivos de configuração do Docker
- `/config`: Configurações do Laravel
- `/routes`: Rotas da API e web
