# GeoFlora Web

Portal web de administracao, visualizacao e monitorizacao do projeto academico GeoFlora, construido com `PHP`, `Yii2`, `PostgreSQL`, `Bootstrap 5` e preparado para integrar `Leaflet.js`.

## Ligacao ao projeto mobile GeoDouro

Este portal usa a mesma base de dados do projeto mobile/backend localizado em `D:\Universidade\3 ANO\2 SEMESTRE\Estagio\Geodouro_Project`.

Pontos de alinhamento ja considerados:

- base de dados partilhada `geodouro`
- schema espelhado a partir de `backend/src/main/resources/schema.sql`
- entidades e tipos alinhados com o backend Kotlin/Spring
- identidade visual inspirada nas cores do tema Android `Geodouro`

Credenciais default identificadas no backend:

```yaml
POSTGRES_DB: geodouro
POSTGRES_USER: postgres
POSTGRES_PASSWORD: postgres
```

## O que ja existe neste repositorio

- bootstrap base do Yii2
- front controller em `web/index.php`
- comando `yii`
- configuracao web/console/db
- `ActiveRecord` para as tabelas principais e auxiliares
- `LoginForm` e `SiteController`
- layout inicial e paginas base de dashboard/login/erro
- migrations espelhadas do schema real

## Como correr localmente

### 1. Confirmar requisitos

No Windows, garante que tens `PHP 8.1+` e `Composer` instalados e acessiveis no terminal:

```powershell
php -v
composer -V
```

### 2. Instalar dependencias

Na pasta do projeto web:

```powershell
cd "D:\Universidade\3 ANO\2 SEMESTRE\Estagio\Geodouro_Project_Web"
composer install
```

Se o `vendor` ainda nao existir, este passo e obrigatorio.

### 3. Confirmar a base de dados

O ficheiro [config/db.php](D:\Universidade\3 ANO\2 SEMESTRE\Estagio\Geodouro_Project_Web\config\db.php) ja aponta por omissao para:

```php
'pgsql:host=127.0.0.1;port=5432;dbname=geodouro'
username: postgres
password: postgres
```

Se o PostgreSQL do backend ainda nao estiver ligado, no projeto mobile/backend podes arrancar com Docker:

```powershell
cd "D:\Universidade\3 ANO\2 SEMESTRE\Estagio\Geodouro_Project\backend"
docker compose up -d
```

### 4. Aplicar migrations

Se estiveres a preparar uma instancia nova da BD:

```powershell
php yii migrate
```

Se ja estiveres a usar a BD existente do projeto mobile e ela ja tiver as tabelas, nao precisas de voltar a correr as migrations.

### 5. Criar um utilizador autenticado para login web

A web autentica apenas registos de `app_user` com `is_authenticated = true`.

Exemplo SQL:

```sql
INSERT INTO app_user (
    is_authenticated,
    guest_label,
    first_name,
    last_name,
    email,
    username,
    password_hash,
    auth_key
) VALUES (
    true,
    'admin-web',
    'Admin',
    'GeoFlora',
    'admin@geoflora.local',
    'admin',
    '<GERAR_COM_YII_SECURITY>',
    'dev-auth-key'
);
```

O ideal e gerar `password_hash` com `Yii::$app->security->generatePasswordHash('a-tua-password')` quando a app ja estiver pronta.

### 6. Arrancar o servidor de desenvolvimento

```powershell
php yii serve --port=8080
```

Abrir no browser:

```text
http://localhost:8080
```

## Direcao visual para a web

Tema base a reproduzir no portal:

- `#3E7A57` verde principal da marca
- `#7FC084` verde de acento
- `#9CCC65` verde claro complementar
- `#F5F5F5` fundo suave para cards
- `#212121` texto principal
- `#757575` texto secundario

A web deve manter um aspeto limpo, tecnico e naturalista, com cards claros, indicadores verdes e enfase em leitura de dados geograficos e biologicos.
