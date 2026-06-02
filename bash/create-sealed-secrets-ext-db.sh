#!/bin/bash

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
KUSTOMIZE_DIR="${PROJECT_ROOT}/kustomize"
CERT_FILE="${PROJECT_ROOT}/public-key-cert.pem"

log_info()  { echo -e "${GREEN}[INFO]${NC} $1"; }
log_warn()  { echo -e "${YELLOW}[WARN]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }
log_step()  { echo -e "${BLUE}[STEP]${NC} $1"; }

check_dependencies() {
    log_step "Verificando dependências..."
    if ! command -v kubectl &> /dev/null; then
        log_error "kubectl não encontrado."
        exit 1
    fi
    if ! command -v kubeseal &> /dev/null; then
        log_error "kubeseal não encontrado. Instale: https://github.com/bitnami-labs/sealed-secrets"
        exit 1
    fi
    log_info "Dependências OK"
}

get_public_cert() {
    log_step "Verificando certificado público do Sealed Secrets..."
    if [ -f "${CERT_FILE}" ]; then
        log_info "Certificado público encontrado: ${CERT_FILE}"
        return 0
    fi
    log_step "Obtendo certificado público do cluster..."
    kubeseal --fetch-cert > "${CERT_FILE}"
    if [ ! -f "${CERT_FILE}" ]; then
        log_error "Falha ao obter certificado público"
        exit 1
    fi
    log_info "Certificado público obtido: ${CERT_FILE}"
}

create_ext_db_sealed_secret() {
    local ENVIRONMENT=$1
    local NAMESPACE=$2

    log_step "Criando Sealed Secret de bancos externos para $ENVIRONMENT..."
    echo ""
    echo -e "${GREEN}[INFO]${NC} === Senhas dos bancos externos - $ENVIRONMENT ==="

    read -sp "Digite DB_SIGAA_PASSWORD (ou Enter para gerar): " SIGAA_PASS
    echo ""
    if [ -z "$SIGAA_PASS" ]; then
        SIGAA_PASS=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-24)
        log_info "DB_SIGAA_PASSWORD gerada: $SIGAA_PASS"
    fi

    read -sp "Digite DB_SISTEMAS_COMUM_PASSWORD (ou Enter para gerar): " SISTEMAS_PASS
    echo ""
    if [ -z "$SISTEMAS_PASS" ]; then
        SISTEMAS_PASS=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-24)
        log_info "DB_SISTEMAS_COMUM_PASSWORD gerada: $SISTEMAS_PASS"
    fi

    TEMP_FILE=$(mktemp)
    cat > "${TEMP_FILE}" << EOF
apiVersion: v1
kind: Secret
metadata:
  name: ext-db-secrets
  namespace: ${NAMESPACE}
  annotations:
    sealedsecrets.bitnami.com/namespace-wide: "true"
type: Opaque
stringData:
  DB_SIGAA_PASSWORD: "${SIGAA_PASS}"
  DB_SISTEMAS_COMUM_PASSWORD: "${SISTEMAS_PASS}"
EOF

    log_info "Secret temporário criado: ${TEMP_FILE}"

    local OUTPUT_DIR="${KUSTOMIZE_DIR}/overlays/${ENVIRONMENT}"
    local OUTPUT_FILE="${OUTPUT_DIR}/sealed-secret-ext-db.yaml"
    kubeseal --format=yaml --cert="${CERT_FILE}" < "$TEMP_FILE" > "$OUTPUT_FILE"
    log_info "Sealed secret criado: $OUTPUT_FILE"
    rm -f "$TEMP_FILE"
    log_info "Arquivo temporário removido"

    local PASS_FILE="${PROJECT_ROOT}/passwords-ext-db-${ENVIRONMENT}.txt"
    cat > "$PASS_FILE" << EOF
DB_SIGAA_PASSWORD=${SIGAA_PASS}
DB_SISTEMAS_COMUM_PASSWORD=${SISTEMAS_PASS}
EOF
    chmod 600 "$PASS_FILE"
    echo -e "${YELLOW}[WARN]${NC} Senhas salvas em: $PASS_FILE (não commitar)"
}

show_menu() {
    echo ""
    echo "=========================================="
    echo "  Sealed Secrets - Bancos Externos"
    echo "=========================================="
    echo "1. Criar para STAGING  (unidatahub-stg)"
    echo "2. Criar para PRODUCTION (unidatahub-prd)"
    echo "3. Criar para STAGING e PRODUCTION"
    echo "0. Sair"
    echo "=========================================="
}

main() {
    echo -e "${GREEN}[INFO]${NC} === Sealed Secrets - Bancos Externos (SIGAA / Sistemas Comum) ==="
    check_dependencies
    get_public_cert

    while true; do
        show_menu
        read -p "Escolha uma opção: " choice

        case $choice in
            1)
                create_ext_db_sealed_secret "staging" "unidatahub-stg"
                log_info "Sealed Secret ext-db STAGING criado!"
                ;;
            2)
                create_ext_db_sealed_secret "production" "unidatahub-prd"
                log_info "Sealed Secret ext-db PRODUCTION criado!"
                ;;
            3)
                create_ext_db_sealed_secret "staging" "unidatahub-stg"
                create_ext_db_sealed_secret "production" "unidatahub-prd"
                log_info "Sealed Secrets ext-db criados para STAGING e PRODUCTION!"
                ;;
            0)
                log_info "Saindo..."
                exit 0
                ;;
            *)
                echo -e "${RED}[ERROR]${NC} Opção inválida"
                ;;
        esac

        echo ""
        read -p "Pressione Enter para continuar..."
    done
}

cd "$PROJECT_ROOT"
main
