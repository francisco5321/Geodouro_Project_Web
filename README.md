# GeoFlora Web

Portal web de administracao, visualizacao e monitorizacao do projeto academico GeoFlora, construido com `PHP`, `Yii2`, `PostgreSQL`, `Bootstrap 5` e preparado para integrar `Leaflet.js`.

## Ligacao ao projeto mobile GeoDouro

Este portal deve usar a mesma base de dados do projeto mobile/backend localizado em `D:\Universidade\3 ANO\2 SEMESTRE\Estagio\Geodouro_Project`.

Pontos de alinhamento ja considerados nesta base:

- base de dados partilhada `geodouro`
- schema espelhado a partir de `backend/src/main/resources/schema.sql`
- entidades e tipos alinhados com o backend Kotlin/Spring
- identidade visual inspirada nas cores do tema Android `Geodouro`

Credenciais default identificadas no projeto mobile/backend:

```yaml
POSTGRES_DB: geodouro
POSTGRES_USER: postgres
POSTGRES_PASSWORD: postgres
```

## Arquitetura inicial proposta

```text
geoflora-web/
+-- assets/
+-- commands/
+-- components/
+-- config/
+-- controllers/
+-- filters/
+-- migrations/
+-- models/
+-- modules/
+-- runtime/
+-- views/
+-- web/
+-- composer.json
+-- yii
```

## Comandos para criar o projeto Yii2

Se quiseres arrancar o projeto a partir do zero com o template basic:

```bash
composer create-project --prefer-dist yiisoft/yii2-app-basic geoflora-web
cd geoflora-web
composer require yiisoft/yii2-bootstrap5
composer require --dev yiisoft/yii2-debug yiisoft/yii2-gii
composer require vlucas/phpdotenv
composer require yiisoft/yii2-httpclient
```

## Nota sobre a base de dados existente

Como a BD ja existe e e partilhada com o mobile/backend, as migrations desta pasta devem ser tratadas como espelho do schema e referencia para novos ambientes. Em desenvolvimento local, a web deve apontar para a mesma instancia PostgreSQL usada pelo backend.

## Primeira fase implementada neste repositorio

- configuracao base da aplicacao web e consola
- models principais com `ActiveRecord`
- suporte a autenticacao Yii em `AppUser`
- models adicionais para `observation_image`, `publication_image` e `taxon_cache`
- migrations alinhadas com o schema real do backend

## Direcao visual para a web

Tema base a reproduzir no portal:

- `#3E7A57` verde principal da marca
- `#7FC084` verde de acento
- `#9CCC65` verde claro complementar
- `#F5F5F5` fundo suave para cards
- `#212121` texto principal
- `#757575` texto secundario

A web deve manter um aspeto limpo, tecnico e naturalista, com cards claros, indicadores verdes e enfase em leitura de dados geograficos e biologicos.
