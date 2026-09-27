<?php
/**
 * Formulário de devolução — B2B e B2C
 *
 * @var array $args
 */

$canal = $args['canal'] ?? 'b2b';
$is_b2b = $canal === 'b2b';

$marcas = devolucoes_mentor_marcas();
$tipos_pedido = devolucoes_mentor_tipos_pedido_form($canal);
$subtipos_erro = devolucoes_mentor_subtipos_erro($canal);

$hero_title = $is_b2b
    ? 'Pedido de Pós Venda'
    : 'Pedido de Apoio Cliente';

$hero_subtitle = $is_b2b
    ? 'Preencha o formulário abaixo para iniciar o processo de pós-venda da sua loja. A nossa equipa analisará o pedido e entrará em contacto consigo.'
    : 'Preencha o formulário abaixo para iniciar o processo de apoio ao cliente. A nossa equipa analisará o seu pedido e entrará em contacto consigo.';
?>

<main class="devolucoes-page devolucoes-page--<?php echo esc_attr($canal); ?>">
    <div class="devolucoes-hero">
        <div class="devolucoes-hero__content">
            <h1 class="devolucoes-hero__title"><?php echo esc_html($hero_title); ?></h1>
            <p class="devolucoes-hero__subtitle"><?php echo esc_html($hero_subtitle); ?></p>
        </div>
    </div>

    <div class="devolucoes-container">
        <form id="form-devolucao" class="devolucoes-form" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="canal" value="<?php echo esc_attr($canal); ?>">

            <section class="form-section" data-section="1">
                <div class="form-section__header">
                    <span class="form-section__number">01</span>
                    <div>
                        <?php if ($is_b2b) : ?>
                            <h2 class="form-section__title">Dados da Loja</h2>
                            <p class="form-section__desc">Informações de contacto da sua empresa</p>
                        <?php else : ?>
                            <h2 class="form-section__title">Dados do Cliente</h2>
                            <p class="form-section__desc">Informações de contacto para acompanhar o seu pedido</p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($is_b2b) : ?>
                    <div class="form-grid form-grid--2">
                        <div class="form-field">
                            <label for="empresa" class="form-label">Nome da Empresa <span class="required">*</span></label>
                            <input type="text" id="empresa" name="empresa" class="form-input" required autocomplete="organization">
                        </div>
                        <div class="form-field">
                            <label for="nif" class="form-label">NIF <span class="required">*</span></label>
                            <input type="text" id="nif" name="nif" class="form-input" required pattern="[0-9]{9}" maxlength="9" inputmode="numeric" placeholder="123456789">
                        </div>
                        <div class="form-field">
                            <label for="contacto" class="form-label">Pessoa de Contacto <span class="required">*</span></label>
                            <input type="text" id="contacto" name="contacto" class="form-input" required autocomplete="name">
                        </div>
                        <div class="form-field">
                            <label for="email" class="form-label">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" class="form-input" required autocomplete="email">
                        </div>
                        <div class="form-field form-field--full">
                            <label for="telefone" class="form-label">Telefone <span class="required">*</span></label>
                            <input type="tel" id="telefone" name="telefone" class="form-input" required autocomplete="tel">
                        </div>
                    </div>
                <?php else : ?>
                    <div class="form-grid form-grid--2">
                        <div class="form-field">
                            <label for="nome" class="form-label">Nome Completo <span class="required">*</span></label>
                            <input type="text" id="nome" name="nome" class="form-input" required autocomplete="name">
                        </div>
                        <div class="form-field">
                            <label for="email" class="form-label">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" class="form-input" required autocomplete="email">
                        </div>
                        <div class="form-field">
                            <label for="telefone" class="form-label">Telefone <span class="required">*</span></label>
                            <input type="tel" id="telefone" name="telefone" class="form-input" required autocomplete="tel">
                        </div>
                        <div class="form-field">
                            <label for="nif" class="form-label">NIF <span class="form-hint-inline">(opcional)</span></label>
                            <input type="text" id="nif" name="nif" class="form-input" pattern="[0-9]{9}" maxlength="9" inputmode="numeric" placeholder="123456789">
                        </div>
                        <div class="form-field form-field--full">
                            <label for="num_encomenda" class="form-label">Nº da Encomenda <span class="form-hint-inline">(opcional)</span></label>
                            <input type="text" id="num_encomenda" name="num_encomenda" class="form-input" placeholder="Ex: ENC-2026-12345">
                            <span class="form-hint">Indique o número da encomenda se o tiver (fatura ou email de confirmação)</span>
                        </div>
                    </div>
                <?php endif; ?>
            </section>

            <section class="form-section" data-section="2">
                <div class="form-section__header">
                    <span class="form-section__number">02</span>
                    <div>
                        <h2 class="form-section__title">Tipo de Pedido</h2>
                        <p class="form-section__desc">Indique o motivo da devolução</p>
                    </div>
                </div>
                <div class="form-field">
                    <label for="tipo_pedido" class="form-label">Tipo de Pedido <span class="required">*</span></label>
                    <div class="form-select-wrapper">
                        <select id="tipo_pedido" name="tipo_pedido" class="form-select" required>
                            <option value="" disabled selected>Selecione o tipo</option>
                            <?php foreach ($tipos_pedido as $value => $label) : ?>
                                <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <svg class="form-select__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                    </div>
                </div>
                <div class="form-field form-field--subtipo" id="subtipo-wrapper" hidden>
                    <label for="subtipo" class="form-label">Detalhe do Erro <span class="required">*</span></label>
                    <div class="form-select-wrapper">
                        <select id="subtipo" name="subtipo" class="form-select">
                            <option value="" disabled selected>Selecione o detalhe</option>
                            <?php foreach ($subtipos_erro as $value => $label) : ?>
                                <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <svg class="form-select__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                    </div>
                </div>
            </section>

            <section class="form-section" data-section="3">
                <div class="form-section__header">
                    <span class="form-section__number">03</span>
                    <div>
                        <h2 class="form-section__title">Marca</h2>
                        <p class="form-section__desc">Selecione a marca do produto</p>
                    </div>
                </div>
                <div class="form-field">
                    <label for="marca" class="form-label">Marca <span class="required">*</span></label>
                    <div class="form-select-wrapper">
                        <select id="marca" name="marca" class="form-select" required>
                            <option value="" disabled selected>Selecione uma marca</option>
                            <?php foreach ($marcas as $marca) : ?>
                                <option value="<?php echo esc_attr($marca); ?>"><?php echo esc_html($marca); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <svg class="form-select__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                    </div>
                </div>
            </section>

            <section class="form-section" data-section="4">
                <div class="form-section__header">
                    <span class="form-section__number">04</span>
                    <div>
                        <h2 class="form-section__title">Detalhes do Produto</h2>
                        <p class="form-section__desc">Referência e quantidade afetada</p>
                    </div>
                </div>
                <div class="form-grid form-grid--2">
                    <?php if ($is_b2b) : ?>
                        <div class="form-field form-field--num-encomenda" id="num-encomenda-wrapper" hidden>
                            <label for="num_encomenda" class="form-label">Nº de Encomenda <span class="required">*</span></label>
                            <input type="text" id="num_encomenda" name="num_encomenda" class="form-input" placeholder="Ex: ENC-2026-12345">
                        </div>
                    <?php endif; ?>
                    <div class="form-field">
                        <label for="referencia" class="form-label">Referência do Produto <span class="required">*</span></label>
                        <input type="text" id="referencia" name="referencia" class="form-input" required placeholder="Ex: KK_001, MC_003">
                        <span class="form-hint">
                            <?php echo $is_b2b
                                ? 'Pode consultar a referência do produto nas faturas da Mentor Company'
                                : 'Indique a referência do produto (fatura, embalagem ou site da marca)'; ?>
                        </span>
                    </div>
                    <div class="form-field">
                        <label for="quantidade" class="form-label">Quantidade Afetada <span class="required">*</span></label>
                        <input type="number" id="quantidade" name="quantidade" class="form-input" required min="1" max="9999" value="1">
                    </div>
                    <div class="form-field form-field--full">
                        <label for="descricao" class="form-label">Descrição do Problema <span class="required">*</span></label>
                        <textarea id="descricao" name="descricao" class="form-textarea" required rows="5" placeholder="Descreva detalhadamente o problema encontrado..."></textarea>
                    </div>
                </div>
            </section>

            <section class="form-section" data-section="5">
                <div class="form-section__header">
                    <span class="form-section__number">05</span>
                    <div>
                        <h2 class="form-section__title">Documentação</h2>
                        <p class="form-section__desc">Anexe fotografias, vídeos e documentos de suporte</p>
                    </div>
                </div>

                <div class="upload-group">
                    <label class="form-label">Fotografias <span class="form-hint-inline">(máx. 10, opcional)</span></label>
                    <div class="upload-zone" id="upload-fotos" data-max="10" data-accept="image/*">
                        <input type="file" id="fotografias" name="fotografias[]" accept="image/*" multiple class="upload-zone__input">
                        <div class="upload-zone__content">
                            <svg class="upload-zone__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <path d="M21 15l-5-5L5 21"/>
                            </svg>
                            <p class="upload-zone__text">Arraste fotografias ou <span class="upload-zone__link">clique para selecionar</span></p>
                            <p class="upload-zone__hint">JPG, PNG, WEBP — até 10 ficheiros</p>
                        </div>
                        <div class="upload-preview" id="preview-fotos"></div>
                    </div>
                </div>

                <div class="upload-group">
                    <label class="form-label">Vídeos <span class="form-hint-inline">(máx. 3, opcional)</span></label>
                    <div class="upload-zone" id="upload-videos" data-max="3" data-accept="video/*">
                        <input type="file" id="videos" name="videos[]" accept="video/*" multiple class="upload-zone__input">
                        <div class="upload-zone__content">
                            <svg class="upload-zone__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <polygon points="23 7 16 12 23 17 23 7"/>
                                <rect x="1" y="5" width="15" height="14" rx="2"/>
                            </svg>
                            <p class="upload-zone__text">Arraste vídeos ou <span class="upload-zone__link">clique para selecionar</span></p>
                            <p class="upload-zone__hint">MP4, MOV, WEBM — até 3 ficheiros</p>
                        </div>
                        <div class="upload-preview" id="preview-videos"></div>
                    </div>
                </div>

                <div class="upload-group">
                    <label class="form-label">Documentos <span class="form-hint-inline">(opcional)</span></label>
                    <div class="upload-zone" id="upload-docs" data-max="5" data-accept=".pdf,image/*">
                        <input type="file" id="documentos" name="documentos[]" accept=".pdf,image/*" multiple class="upload-zone__input">
                        <div class="upload-zone__content">
                            <svg class="upload-zone__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                            </svg>
                            <p class="upload-zone__text">Arraste documentos ou <span class="upload-zone__link">clique para selecionar</span></p>
                            <p class="upload-zone__hint">PDF, JPG, PNG</p>
                        </div>
                        <div class="upload-preview" id="preview-docs"></div>
                    </div>
                </div>
            </section>

            <section class="form-section form-section--confirm" data-section="6">
                <div class="form-section__header">
                    <span class="form-section__number">06</span>
                    <div>
                        <h2 class="form-section__title">Confirmação</h2>
                        <p class="form-section__desc">Confirme as declarações abaixo</p>
                    </div>
                </div>
                <div class="form-checkboxes">
                    <label class="form-checkbox">
                        <input type="checkbox" name="confirmacao_verdade" value="1" required>
                        <span class="form-checkbox__box">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        </span>
                        <span class="form-checkbox__label">Confirmo que as informações prestadas são verdadeiras.</span>
                    </label>
                    <label class="form-checkbox">
                        <input type="checkbox" name="confirmacao_integridade" value="1" required>
                        <span class="form-checkbox__box">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        </span>
                        <span class="form-checkbox__label">Confirmo que o produto não foi alterado ou reparado por terceiros.</span>
                    </label>
                </div>
            </section>

            <div class="form-submit">
                <button type="submit" class="btn-submit" id="btn-submit">
                    <span class="btn-submit__text">Submeter Pedido</span>
                    <svg class="btn-submit__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"/>
                        <polyline points="12 5 19 12 12 19"/>
                    </svg>
                    <span class="btn-submit__loader" hidden>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20"/></svg>
                    </span>
                </button>
            </div>
        </form>
    </div>
</main>
