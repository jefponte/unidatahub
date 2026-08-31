# Unidatahub

This project is a Laravel-based API serving as a template for new projects with common functionalities.

## Requirements

- Docker
- Docker Compose

## Development Environment Setup

### Step 1: Copy `.env.example` to `.env` and Set Environment Variables

Copy the example environment configuration file and set up the necessary environment variables for the application.

    cp .env.example .env

In the `.env` file, configure the database connection settings:


    DB_LOCAL_DRIVER=mysql
    DB_LOCAL_HOST=172.23.112.1
    DB_LOCAL_PORT=3306
    DB_LOCAL_DATABASE=unidatahub
    DB_LOCAL_USERNAME=user
    DB_LOCAL_PASSWORD=secret


    DB_SIGAA_DRIVER=mysql
    DB_SIGAA_HOST=172.23.112.1
    DB_SIGAA_PORT=3306
    DB_SIGAA_DATABASE=unidatahub
    DB_SIGAA_USERNAME=user
    DB_SIGAA_PASSWORD=secret


    DB_SISTEMAS_COMUM_DRIVER=mysql
    DB_SISTEMAS_COMUM_HOST=172.23.112.1
    DB_SISTEMAS_COMUM_PORT=3306
    DB_SISTEMAS_COMUM_DATABASE=unidatahub
    DB_SISTEMAS_COMUM_USERNAME=user
    DB_SISTEMAS_COMUM_PASSWORD=secret


This step sets up the environment variables required for the application to connect to the MySQL database.

### Step 2: Build and Start Containers with Docker Compose

Use Docker Compose to build and start the containers in detached mode.

    docker compose up -d --build

This command will build the Docker image if needed and start the containers in the background.

### Step 3: Install Laravel Dependencies Inside the Container

Run the following command to install dependencies using Composer:

    docker exec -it unidatahub bash -c "composer install"

### Step 4: Run Database Migrations

With the environment up, run the migrations:

    docker exec -it unidatahub bash -c "php artisan migrate"

    docker exec -it unidatahub bash -c "php artisan db:seed"


### Step 5: Run Database SIGAA Migrations

    docker exec unidatahub php artisan migrate --database=sigaa --path=database/migrations/sigaa

    docker exec unidatahub php artisan db:seed --class="Database\Seeders\Sigaa\VwUsuariosCatracaSeeder"

### Step 5: Generate Application Key

To secure the application, generate a new app key:

    docker exec -it unidatahub bash -c "php artisan key:generate"

    docker exec -it unidatahub bash -c "php artisan passport:keys"

    docker exec -it unidatahub bash -c "php artisan passport:client --client"


### Step 6: Access the Application

The API will be available at [http://localhost](http://localhost).

## Useful Commands

- **Stop containers**:

      docker-compose down

- **Restart containers**:

      docker-compose up -d

- **Access the container**:

      docker exec -it unidatahub bash


## Notes

- Ensure Docker and Docker Compose are installed and up to date.
- Review directory permissions if needed, especially for `storage` and `bootstrap/cache`.

---

## Kubernetes Deployment

### Certificados Públicos do Sealed Secrets

Os certificados de cada cluster estão em `bash/tmp/`:

| Arquivo | Cluster | Ambiente |
|---|---|---|
| `sc-pub-cert-k8sc1-red.pem` | k8sc1-red | Staging |
| `sc-pub-cert-k8sc2-for.pem` | k8sc2-for | Production |

Para development, obtenha no bastion o certificado público do cluster em que o
namespace `unidatahub-api-dev` está hospedado. O certificado não contém a chave
privada. No bastion, confirme primeiro o contexto e o namespace:

```bash
kubectl config current-context
kubectl get namespace unidatahub-api-dev

kubeseal --fetch-cert \
  --controller-name=sealed-secrets-controller \
  --controller-namespace=kube-system \
  > /tmp/sc-pub-cert-development.pem
```

Na máquina local, a partir da raiz deste repositório, copie somente o certificado
público (substitua `<bastion>` pelo host configurado no SSH):

```bash
scp <bastion>:/tmp/sc-pub-cert-development.pem \
  bash/tmp/sc-pub-cert-development.pem

openssl x509 -in bash/tmp/sc-pub-cert-development.pem \
  -noout -subject -issuer -dates
```

Não reutilize automaticamente o certificado de staging ou production. Ele só é
válido para development quando os ambientes usam o mesmo controller Sealed
Secrets no mesmo cluster.

### Gerar Sealed Secrets de development

Os scripts abaixo geram os manifests diretamente em
`kustomize/overlays/development/`. Informe sempre o certificado do cluster de
development; um secret selado para outro cluster não poderá ser descriptografado.

```bash
# MariaDB e APP_KEY do Laravel
./bash/create-sealed-secrets-development.sh \
  bash/tmp/sc-pub-cert-development.pem

# Bancos externos (SIGAA e Sistemas Comum)
./bash/create-sealed-secrets-ext-db-development.sh \
  bash/tmp/sc-pub-cert-development.pem
```

Depois da primeira geração, migre
`kustomize/overlays/development/kustomization.yaml` como já é feito em staging:

1. Adicione os três arquivos `sealed-secret-*.yaml` à seção `resources`.
2. Remova de `secretGenerator` as entradas `mariadb-secret-env` e
  `laravel-secrets` (mantenha `passport-keys`).
3. Remova `SIGAA_DB_PASSWORD` e `SISTEMAS_COMUM_DB_PASSWORD` do
  `configMapGenerator`.
4. Adicione a `ext-db-secrets` ao `envFrom` do Deployment, usando o mesmo patch
  presente no overlay de staging.
5. Valide com:

```bash
kubectl kustomize kustomize/overlays/development > /dev/null
```

Pressionar Enter em uma senha gera um valor aleatório. Os valores originais são
gravados em `passwords-*-development.txt`, com permissão `600` e já cobertos pelo
`.gitignore`. Guarde-os em um cofre de senhas e remova as cópias locais quando não
forem mais necessárias. Os YAMLs `sealed-secret-*.yaml` gerados devem ser
commitados para que a pipeline/Argo CD aplique a alteração; não aplique mudanças
manualmente no cluster de deployment.

### Gerar Sealed Secrets — `ext-db-secrets` (SIGAA / Sistemas Comum)

**Staging:**
```bash
kubectl create secret generic ext-db-secrets \
  --from-literal=SIGAA_DB_PASSWORD='<senha>' \
  --from-literal=SISTEMAS_COMUM_DB_PASSWORD='<senha>' \
  --namespace=unidatahub-api-stg \
  --dry-run=client -o yaml | \
kubeseal --format=yaml --cert=bash/tmp/sc-pub-cert-k8sc1-red.pem \
  > kustomize/overlays/staging/sealed-secret-ext-db.yaml
```

**Production:**
```bash
kubectl create secret generic ext-db-secrets \
  --from-literal=SIGAA_DB_PASSWORD='<senha>' \
  --from-literal=SISTEMAS_COMUM_DB_PASSWORD='<senha>' \
  --namespace=unidatahub-api-prd \
  --dry-run=client -o yaml | \
kubeseal --format=yaml --cert=bash/tmp/sc-pub-cert-k8sc2-for.pem \
  > kustomize/overlays/production/sealed-secret-ext-db.yaml
```

### Gerar Sealed Secrets — `laravel-secrets` (APP_KEY)

**Staging:**
```bash
kubectl create secret generic laravel-secrets \
  --from-literal=APP_KEY='base64:<chave>' \
  --namespace=unidatahub-api-stg \
  --dry-run=client -o yaml | \
kubeseal --format=yaml --cert=bash/tmp/sc-pub-cert-k8sc1-red.pem \
  > kustomize/overlays/staging/sealed-secret-laravel-secrets.yaml
```

**Production:**
```bash
kubectl create secret generic laravel-secrets \
  --from-literal=APP_KEY='base64:<chave>' \
  --namespace=unidatahub-api-prd \
  --dry-run=client -o yaml | \
kubeseal --format=yaml --cert=bash/tmp/sc-pub-cert-k8sc2-for.pem \
  > kustomize/overlays/production/sealed-secret-laravel-secrets.yaml
```

### Gerar Sealed Secrets — `mariadb-secret-env`

**Staging:**
```bash
kubectl create secret generic mariadb-secret-env \
  --from-literal=DB_USERNAME='unidatahub_stag_user' \
  --from-literal=DB_PASSWORD='<senha_app>' \
  --from-literal=MYSQL_ROOT_PASSWORD='<senha_root>' \
  --from-literal=MYSQL_PASSWORD='<senha_app>' \
  --from-literal=SST_USER='sstuser' \
  --from-literal=SST_PASSWORD='<senha_sst>' \
  --from-literal=MARIADB_REPLICATION_PASSWORD='<senha_repl>' \
  --namespace=unidatahub-api-stg \
  --dry-run=client -o yaml | \
kubeseal --format=yaml --cert=bash/tmp/sc-pub-cert-k8sc1-red.pem \
  > kustomize/overlays/staging/sealed-secret-mariadb-credentials.yaml
```

**Production:**
```bash
kubectl create secret generic mariadb-secret-env \
  --from-literal=DB_USERNAME='unidatahub_prod_user' \
  --from-literal=DB_PASSWORD='<senha_app>' \
  --from-literal=MYSQL_ROOT_PASSWORD='<senha_root>' \
  --from-literal=MYSQL_PASSWORD='<senha_app>' \
  --from-literal=SST_USER='sstuser' \
  --from-literal=SST_PASSWORD='<senha_sst>' \
  --from-literal=MARIADB_REPLICATION_PASSWORD='<senha_repl>' \
  --namespace=unidatahub-api-prd \
  --dry-run=client -o yaml | \
kubeseal --format=yaml --cert=bash/tmp/sc-pub-cert-k8sc2-for.pem \
  > kustomize/overlays/production/sealed-secret-mariadb-credentials.yaml
```

> As senhas geradas são salvas em `passwords-mariadb-staging.txt` e `passwords-mariadb-production.txt` (não commitar).

### Aplicar no cluster

```bash
# Staging
kubectl apply -k kustomize/overlays/staging

# Production
kubectl apply -k kustomize/overlays/production
```

