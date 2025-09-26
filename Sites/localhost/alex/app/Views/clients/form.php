<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3">
                        <i class="bi bi-people"></i> <?= $action === 'create' ? 'Cadastrar Importador' : 'Editar Importador' ?>
                    </h1>
                    <p class="text-muted">Preencha os dados do importador</p>
                </div>
                <div>
                    <a href="<?= BASE_URL ?>clients" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensagens -->
    <?php if (isset($_SESSION['error'])): ?>
        <script>
            window.pendingMessages = window.pendingMessages || [];
            window.pendingMessages.push({
                type: 'error',
                message: '<?= htmlspecialchars($_SESSION['error'], ENT_QUOTES) ?>'
            });
        </script>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Formulário -->
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-form"></i> Dados do Importador
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>api/clients/<?= $action === 'create' ? 'create' : 'update' ?>" method="POST">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="id" value="<?= $client['id'] ?>">
                        <?php endif; ?>

                        <!-- Tipo de Cliente (oculto) -->
                        <input type="hidden" id="type" name="type" value="PJ">

                        <!-- Identificação -->
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h6 class="text-primary"><i class="bi bi-person-badge"></i> Identificação</h6>
                                <hr class="mt-1">
                            </div>

                            <!-- CNPJ -->
                            <div class="col-md-4">
                                <label for="document" class="form-label">
                                    <i class="bi bi-card-text"></i> CNPJ *
                                </label>
                                <input type="text" class="form-control" id="document" name="document"
                                       value="<?= htmlspecialchars($client['document'] ?? '') ?>"
                                       placeholder="00.000.000/0000-00" required>
                                <div class="form-text" id="documentHelp">Digite o CNPJ para buscar dados automaticamente</div>
                                <button type="button" class="btn btn-sm btn-primary mt-2" id="btnBuscarCNPJ" style="display: none;">
                                    <i class="bi bi-search"></i> Buscar Dados
                                </button>
                            </div>

                            <!-- Razão Social -->
                            <div class="col-md-8">
                                <label for="name" class="form-label">
                                    <i class="bi bi-building"></i> Razão Social *
                                </label>
                                <input type="text" class="form-control" id="name" name="name"
                                       value="<?= htmlspecialchars($client['name'] ?? '') ?>"
                                       placeholder="Razão social da empresa" required>
                            </div>

                            <!-- IE -->
                            <div class="col-md-4">
                                <label for="ie" class="form-label">
                                    <i class="bi bi-file-text"></i> Inscrição Estadual
                                </label>
                                <input type="text" class="form-control" id="ie" name="ie"
                                       value="<?= htmlspecialchars($client['ie'] ?? '') ?>"
                                       placeholder="123.456.789.012">
                            </div>

                            <!-- IM -->
                            <div class="col-md-4">
                                <label for="im" class="form-label">
                                    <i class="bi bi-file-text"></i> Inscrição Municipal
                                </label>
                                <input type="text" class="form-control" id="im" name="im"
                                       value="<?= htmlspecialchars($client['im'] ?? '') ?>"
                                       placeholder="12345678">
                            </div>
                        </div>

                        <!-- Endereço -->
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h6 class="text-success"><i class="bi bi-geo-alt"></i> Endereço</h6>
                                <hr class="mt-1">
                            </div>

                            <div class="col-md-6">
                                <label for="address" class="form-label">
                                    <i class="bi bi-house"></i> Logradouro
                                </label>
                                <input type="text" class="form-control" id="address" name="address"
                                       value="<?= htmlspecialchars($client['address'] ?? '') ?>"
                                       placeholder="Rua, Av, etc">
                            </div>

                            <div class="col-md-2">
                                <label for="number" class="form-label">Número</label>
                                <input type="text" class="form-control" id="number" name="number"
                                       value="<?= htmlspecialchars($client['number'] ?? '') ?>"
                                       placeholder="123">
                            </div>

                            <div class="col-md-4">
                                <label for="complement" class="form-label">Complemento</label>
                                <input type="text" class="form-control" id="complement" name="complement"
                                       value="<?= htmlspecialchars($client['complement'] ?? '') ?>"
                                       placeholder="Apt, Sala, etc">
                            </div>

                            <div class="col-md-4">
                                <label for="neighborhood" class="form-label">Bairro</label>
                                <input type="text" class="form-control" id="neighborhood" name="neighborhood"
                                       value="<?= htmlspecialchars($client['neighborhood'] ?? '') ?>"
                                       placeholder="Nome do bairro">
                            </div>

                            <div class="col-md-4">
                                <label for="city" class="form-label">Cidade</label>
                                <input type="text" class="form-control" id="city" name="city"
                                       value="<?= htmlspecialchars($client['city'] ?? '') ?>"
                                       placeholder="Nome da cidade">
                            </div>

                            <div class="col-md-2">
                                <label for="state" class="form-label">UF</label>
                                <select class="form-select select2" id="state" name="state">
                                    <option value="">UF</option>
                                    <option value="AC" <?= ($client['state'] ?? '') === 'AC' ? 'selected' : '' ?>>AC</option>
                                    <option value="AL" <?= ($client['state'] ?? '') === 'AL' ? 'selected' : '' ?>>AL</option>
                                    <option value="AP" <?= ($client['state'] ?? '') === 'AP' ? 'selected' : '' ?>>AP</option>
                                    <option value="AM" <?= ($client['state'] ?? '') === 'AM' ? 'selected' : '' ?>>AM</option>
                                    <option value="BA" <?= ($client['state'] ?? '') === 'BA' ? 'selected' : '' ?>>BA</option>
                                    <option value="CE" <?= ($client['state'] ?? '') === 'CE' ? 'selected' : '' ?>>CE</option>
                                    <option value="DF" <?= ($client['state'] ?? '') === 'DF' ? 'selected' : '' ?>>DF</option>
                                    <option value="ES" <?= ($client['state'] ?? '') === 'ES' ? 'selected' : '' ?>>ES</option>
                                    <option value="GO" <?= ($client['state'] ?? '') === 'GO' ? 'selected' : '' ?>>GO</option>
                                    <option value="MA" <?= ($client['state'] ?? '') === 'MA' ? 'selected' : '' ?>>MA</option>
                                    <option value="MT" <?= ($client['state'] ?? '') === 'MT' ? 'selected' : '' ?>>MT</option>
                                    <option value="MS" <?= ($client['state'] ?? '') === 'MS' ? 'selected' : '' ?>>MS</option>
                                    <option value="MG" <?= ($client['state'] ?? '') === 'MG' ? 'selected' : '' ?>>MG</option>
                                    <option value="PA" <?= ($client['state'] ?? '') === 'PA' ? 'selected' : '' ?>>PA</option>
                                    <option value="PB" <?= ($client['state'] ?? '') === 'PB' ? 'selected' : '' ?>>PB</option>
                                    <option value="PR" <?= ($client['state'] ?? '') === 'PR' ? 'selected' : '' ?>>PR</option>
                                    <option value="PE" <?= ($client['state'] ?? '') === 'PE' ? 'selected' : '' ?>>PE</option>
                                    <option value="PI" <?= ($client['state'] ?? '') === 'PI' ? 'selected' : '' ?>>PI</option>
                                    <option value="RJ" <?= ($client['state'] ?? '') === 'RJ' ? 'selected' : '' ?>>RJ</option>
                                    <option value="RN" <?= ($client['state'] ?? '') === 'RN' ? 'selected' : '' ?>>RN</option>
                                    <option value="RS" <?= ($client['state'] ?? '') === 'RS' ? 'selected' : '' ?>>RS</option>
                                    <option value="RO" <?= ($client['state'] ?? '') === 'RO' ? 'selected' : '' ?>>RO</option>
                                    <option value="RR" <?= ($client['state'] ?? '') === 'RR' ? 'selected' : '' ?>>RR</option>
                                    <option value="SC" <?= ($client['state'] ?? '') === 'SC' ? 'selected' : '' ?>>SC</option>
                                    <option value="SP" <?= ($client['state'] ?? '') === 'SP' ? 'selected' : '' ?>>SP</option>
                                    <option value="SE" <?= ($client['state'] ?? '') === 'SE' ? 'selected' : '' ?>>SE</option>
                                    <option value="TO" <?= ($client['state'] ?? '') === 'TO' ? 'selected' : '' ?>>TO</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="zip_code" class="form-label">CEP</label>
                                <input type="text" class="form-control" id="zip_code" name="zip_code"
                                       value="<?= htmlspecialchars($client['zip_code'] ?? '') ?>"
                                       placeholder="12345-678">
                            </div>
                        </div>

                        <!-- Contato -->
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h6 class="text-info"><i class="bi bi-telephone"></i> Contato</h6>
                                <hr class="mt-1">
                            </div>

                            <div class="col-md-3">
                                <label for="phone" class="form-label">
                                    <i class="bi bi-telephone"></i> Telefone
                                </label>
                                <input type="text" class="form-control" id="phone" name="phone"
                                       value="<?= htmlspecialchars($client['phone'] ?? '') ?>"
                                       placeholder="(11) 3000-0000">
                            </div>

                            <div class="col-md-3">
                                <label for="mobile" class="form-label">
                                    <i class="bi bi-phone"></i> Celular
                                </label>
                                <input type="text" class="form-control" id="mobile" name="mobile"
                                       value="<?= htmlspecialchars($client['mobile'] ?? '') ?>"
                                       placeholder="(11) 99999-9999">
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope"></i> Email
                                </label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?= htmlspecialchars($client['email'] ?? '') ?>"
                                       placeholder="contato@empresa.com.br">
                            </div>

                            <div class="col-md-6">
                                <label for="contact_name" class="form-label">
                                    <i class="bi bi-person"></i> Nome do Contato
                                </label>
                                <input type="text" class="form-control" id="contact_name" name="contact_name"
                                       value="<?= htmlspecialchars($client['contact_name'] ?? '') ?>"
                                       placeholder="Nome da pessoa de contato">
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h6 class="text-warning"><i class="bi bi-power"></i> Status</h6>
                                <hr class="mt-1">
                            </div>

                            <div class="col-md-3">
                                <label for="is_active" class="form-label">
                                    <i class="bi bi-power"></i> Status
                                </label>
                                <select class="form-select select2" id="is_active" name="is_active">
                                    <option value="1" <?= ($client['is_active'] ?? true) ? 'selected' : '' ?>>Ativo</option>
                                    <option value="0" <?= isset($client['is_active']) && !$client['is_active'] ? 'selected' : '' ?>>Inativo</option>
                                </select>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="<?= BASE_URL ?>clients" class="btn btn-secondary">
                                        <i class="bi bi-x-circle"></i> Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i>
                                        <?= $action === 'create' ? 'Cadastrar Importador' : 'Atualizar Importador' ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.pendingScripts = window.pendingScripts || [];
window.pendingScripts.push(function() {
    // Inicializar Select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        allowClear: false
    });

    // Máscara para CNPJ
    $('#document').on('input', function() {
        // Máscara CNPJ
        this.value = this.value.replace(/\D/g, '').replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');

        // Mostrar botão de busca quando CNPJ tiver 14 dígitos
        const cleanCNPJ = this.value.replace(/\D/g, '');
        if (cleanCNPJ.length === 14) {
            $('#btnBuscarCNPJ').show();
        } else {
            $('#btnBuscarCNPJ').hide();
        }
    });

    // Buscar dados do CNPJ
    $('#btnBuscarCNPJ, #document').on('click blur', function(e) {
        if (e.type === 'click' || (e.type === 'blur' && $(this).attr('id') === 'document')) {
            const cnpj = $('#document').val().replace(/\D/g, '');
            if (cnpj.length === 14) {
                buscarDadosCNPJ(cnpj);
            }
        }
    });

    // Máscara para CEP
    $('#zip_code').on('input', function() {
        this.value = this.value.replace(/\D/g, '').replace(/(\d{5})(\d{3})/, '$1-$2');
    });

    // Máscara para telefones
    $('#phone, #mobile').on('input', function() {
        this.value = this.value.replace(/\D/g, '').replace(/(\d{2})(\d{4,5})(\d{4})/, '($1) $2-$3');
    });
});

// Função para exibir mensagens com SweetAlert2 Toast
function showMessage(type, message) {
    let icon = 'info';

    switch(type) {
        case 'success':
            icon = 'success';
            break;
        case 'error':
            icon = 'error';
            break;
        case 'warning':
            icon = 'warning';
            break;
    }

    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: icon,
        title: message,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}

// Função para buscar dados do CNPJ
function buscarDadosCNPJ(cnpj) {
    // Mostrar loading
    $('#btnBuscarCNPJ').html('<i class="bi bi-hourglass-split"></i> Buscando...').prop('disabled', true);

    // Buscar na API Brasil API (gratuita)
    $.ajax({
        url: `https://brasilapi.com.br/api/cnpj/v1/${cnpj}`,
        method: 'GET',
        success: function(data) {
            // Log dos dados recebidos da Brasil API
            console.log('Dados Brasil API:', data);

            // Preencher campos com os dados retornados
            if (data.razao_social) {
                $('#name').val(data.razao_social);
            }
            if (data.logradouro) {
                $('#address').val(data.logradouro);
            }
            if (data.numero) {
                $('#number').val(data.numero);
            }
            if (data.complemento) {
                $('#complement').val(data.complemento);
            }
            if (data.bairro) {
                $('#neighborhood').val(data.bairro);
            }
            if (data.municipio) {
                $('#city').val(data.municipio);
            }
            if (data.uf) {
                $('#state').val(data.uf).trigger('change');
            }
            if (data.cep) {
                $('#zip_code').val(data.cep.replace(/\D/g, '').replace(/(\d{5})(\d{3})/, '$1-$2'));
            }
            if (data.ddd_telefone_1) {
                const telefone = data.ddd_telefone_1.replace(/\D/g, '');
                if (telefone.length > 10) {
                    $('#mobile').val(telefone.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3'));
                } else if (telefone.length > 0) {
                    $('#phone').val(telefone.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3'));
                }
            }
            if (data.email) {
                $('#email').val(data.email.toLowerCase());
            }

            // Preencher nome do contato com representante legal ou sócio
            let nomeContato = '';
            if (data.legal_nature && data.legal_nature.representatives && data.legal_nature.representatives.length > 0) {
                nomeContato = data.legal_nature.representatives[0].name;
            } else if (data.partners && data.partners.length > 0) {
                nomeContato = data.partners[0].name;
            }

            if (nomeContato) {
                $('#contact_name').val(nomeContato);
            }

            // Mensagem de sucesso
            showMessage('success', 'Dados do CNPJ importados com sucesso!');
        },
        error: function() {
            // Se falhar, tentar ReceitaWS como alternativa
            $.ajax({
                url: `https://receitaws.com.br/v1/cnpj/${cnpj}`,
                method: 'GET',
                dataType: 'jsonp',
                success: function(data) {
                    // Log dos dados recebidos da ReceitaWS
                    console.log('Dados ReceitaWS:', data);

                    if (data.status === 'ERROR') {
                        showMessage('error', 'CNPJ não encontrado ou inválido');
                        return;
                    }

                    // Preencher campos
                    if (data.nome) {
                        $('#name').val(data.nome);
                    }
                    if (data.logradouro) {
                        $('#address').val(data.logradouro);
                    }
                    if (data.numero) {
                        $('#number').val(data.numero);
                    }
                    if (data.complemento) {
                        $('#complement').val(data.complemento);
                    }
                    if (data.bairro) {
                        $('#neighborhood').val(data.bairro);
                    }
                    if (data.municipio) {
                        $('#city').val(data.municipio);
                    }
                    if (data.uf) {
                        $('#state').val(data.uf).trigger('change');
                    }
                    if (data.cep) {
                        $('#zip_code').val(data.cep.replace(/\D/g, '').replace(/(\d{5})(\d{3})/, '$1-$2'));
                    }
                    if (data.telefone) {
                        const telefone = data.telefone.replace(/\D/g, '');
                        $('#phone').val(telefone.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3'));
                    }
                    if (data.email) {
                        $('#email').val(data.email.toLowerCase());
                    }

                    // Preencher nome do contato com representante legal ou sócio (ReceitaWS)
                    let nomeContato = '';

                    // Verificar se tem representante legal
                    if (data.qsa && data.qsa.length > 0) {
                        // Procurar primeiro por administrador ou representante legal
                        const representante = data.qsa.find(pessoa =>
                            pessoa.qual && (
                                pessoa.qual.toLowerCase().includes('administrador') ||
                                pessoa.qual.toLowerCase().includes('representante') ||
                                pessoa.qual.toLowerCase().includes('diretor')
                            )
                        );

                        if (representante && representante.nome) {
                            nomeContato = representante.nome;
                        } else if (data.qsa[0] && data.qsa[0].nome) {
                            // Se não encontrar representante específico, usar o primeiro sócio
                            nomeContato = data.qsa[0].nome;
                        }
                    }

                    if (nomeContato) {
                        $('#contact_name').val(nomeContato);
                    }

                    showMessage('success', 'Dados do CNPJ importados com sucesso!');
                },
                error: function() {
                    showMessage('warning', 'Não foi possível buscar os dados do CNPJ. Por favor, preencha manualmente.');
                }
            });
        },
        complete: function() {
            // Restaurar botão
            $('#btnBuscarCNPJ').html('<i class="bi bi-search"></i> Buscar Dados').prop('disabled', false);
        }
    });
}
</script>