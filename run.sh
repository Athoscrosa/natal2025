#!/bin/bash

cd /home/atosautomacao/

rm -R vendor/
rm -R composer.lock

# Configurar e executar o Composer
export COMPOSER_ALLOW_SUPERUSER=1
echo "Instalando dependências do Composer..."
composer install --no-interaction --prefer-dist --optimize-autoloader
composer update --no-interaction
composer dump-autoload -o

PG_USER="athos"
PG_PASS="athos777"
PG_DB="athos"
############################################################
# 1) Criar usuário se não existir
############################################################
create_user_if_not_exists() {
    echo ">> Verificando se o usuário '${PG_USER}' existe..."

    USER_EXISTS=$(sudo -u postgres psql -tAc "SELECT 1 FROM pg_roles WHERE rolname='${PG_USER}'")

    if [ "$USER_EXISTS" = "1" ]; then
        echo "   - Usuário já existe. Nada será feito."
    else
        echo "   - Usuário não existe. Criando usuário..."
        sudo -u postgres psql -c "CREATE USER ${PG_USER} WITH PASSWORD '${PG_PASS}';"
        echo "   - Usuário criado com sucesso."
    fi
}
############################################################
# 2) Criar banco se não existir e definir owner
############################################################
create_database_if_not_exists() {
    echo ">> Verificando se o banco '${PG_DB}' existe..."

    DB_EXISTS=$(sudo -u postgres psql -tAc "SELECT 1 FROM pg_database WHERE datname='${PG_DB}'")

    if [ "$DB_EXISTS" = "1" ]; then
        echo "   - Banco já existe. Garantindo que o owner é '${PG_USER}'..."
        sudo -u postgres psql -c "ALTER DATABASE ${PG_DB} OWNER TO ${PG_USER};"
    else
        echo "   - Banco não existe. Criando banco..."
        sudo -u postgres psql -c "CREATE DATABASE ${PG_DB} OWNER ${PG_USER};"
        echo "   - Banco criado com sucesso."
    fi
}
############################################################
# 3) Criar tabelas e view se não existirem
############################################################
create_schema_objects() {
    echo ">> Conectando ao banco '${PG_DB}' e criando objetos..."
sudo -u postgres psql -d "${PG_DB}" <<EOF

create table uf(
	id bigserial primary key,
	sigla text,
	nome text, 
	data_cadastro timestamp default current_timestamp,
	data_alteracao timestamp default current_timestamp
);

create table cidade(
	id bigserial primary key, 
	id_uf bigint,
	nome text,
	ibge text,
	data_cadastro timestamp default current_timestamp,
	data_alteracao timestamp default current_timestamp,
	constraint cidade_id_uf foreign key (id_uf) references uf(id)
);

-- Tabela usuario cliente

create table cliente(
	id bigserial primary key,
	nome_fantasia text,
	sobrenome_razao text,
	cpf_cnpj text,
	rg_ie text,
	data_nascimento_abertura date,
	data_cadastro timestamp default current_timestamp,
	data_alteracao timestamp default current_timestamp
);

    -- Tabela usuario
    CREATE TABLE IF NOT EXISTS usuario (
        id bigserial PRIMARY KEY,
        nome text,
        sobrenome text,
        cpf text,
        rg text,
        data_nascimento date,
        senha text,
        ativo boolean DEFAULT false,
        administrador boolean DEFAULT false,
        codigo_verificacao text,
        data_cadastro timestamp DEFAULT CURRENT_TIMESTAMP,
        data_alteracao timestamp DEFAULT CURRENT_TIMESTAMP
);

-- Tabela empresa

create table empresa(
	id bigserial primary key,
	nome_fantasia text,
	sobrenome_razao text,
	cpf_cnpj text,
	rg_ie text,
	data_nascimento_abertura date,
	data_cadastro timestamp default current_timestamp,
	data_alteracao timestamp default current_timestamp
);

-- Tabela fornecedor

create table fornecedor(
	id bigserial primary key,
	nome_fantasia text,
	sobrenome_razao text,
	cpf_cnpj text,
	rg_ie text,
	data_nascimento_abertura date,
	data_cadastro timestamp default current_timestamp,
	data_alteracao timestamp default current_timestamp
);

-- Tabela endereco

create table endereco(
	id bigserial primary key, 
	id_cidade bigint,
	id_cliente bigint,
	id_usuario bigint,
	id_empresa bigint,
	id_fornecedor bigint,
	nome text,
	cep text,
	numero text,
	logradouro text,
	bairro text,
	complemento text,
	referencia text,
	data_cadastro timestamp default current_timestamp,
	data_alteracao timestamp default current_timestamp,
	constraint endereco_id_cidade foreign key (id_cidade) references cidade(id),
	constraint contato_id_usuario foreign key (id_usuario) references usuario(id),
	constraint contato_id_cliente foreign key (id_cliente) references cliente(id),
	constraint contato_id_empresa foreign key (id_empresa) references empresa(id),
	constraint contato_id_fornecedor foreign key (id_fornecedor) references fornecedor(id)
);


    -- Tabela contato
    CREATE TABLE IF NOT EXISTS contato (
        id bigserial PRIMARY KEY,
        id_usuario bigint,
        tipo text,
        contato text,
        data_cadastro timestamp,
        data_alteracao timestamp,
        CONSTRAINT contato_id_usuario_fkey FOREIGN KEY (id_usuario)
            REFERENCES public.usuario (id)
            ON UPDATE NO ACTION
            ON DELETE NO ACTION
    );
    -- View vw_usuario_contatos
    CREATE OR REPLACE VIEW vw_usuario_contatos AS
    SELECT u.id,
        u.nome,
        u.sobrenome,
        u.cpf,
        u.rg,
        u.senha,
        u.ativo,
        u.administrador,
        u.codigo_verificacao,
        MAX(CASE WHEN c.tipo = 'email' THEN c.contato ELSE NULL END) AS email,
        MAX(CASE WHEN c.tipo = 'celular' THEN c.contato ELSE NULL END) AS celular,
        MAX(CASE WHEN c.tipo = 'whatsapp' THEN c.contato ELSE NULL END) AS whatsapp,
        u.data_cadastro,
        u.data_alteracao
    FROM usuario u
    LEFT JOIN contato c ON c.id_usuario = u.id
    GROUP BY u.id, u.nome, u.sobrenome, u.cpf, u.rg, u.data_cadastro, u.data_alteracao;
EOF
    echo "   - Tabelas e view verificadas/criadas com sucesso."
}

############################################################
# Execução das funções
############################################################

create_user_if_not_exists
create_database_if_not_exists
create_schema_objects 

echo ">> Processo concluído!"

service nginx reload