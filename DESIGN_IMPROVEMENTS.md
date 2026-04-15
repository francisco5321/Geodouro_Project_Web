# Melhorias de Design - Geodouro Project Web

## ✅ Implementações Completadas

### 1. **Sistema de Ícones Unificado** (FontAwesome 6.5.1)
- Integrado via CDN dentro do AppAsset
- Suporte a 2.000+ ícones
- Ícones na navegação, botões, formulários e componentes

### 2. **Componentes Reutilizáveis (Yii Widgets)**

#### `StatCard` - Exibir Estatísticas
```php
<?= StatCard::widget([
    'label' => 'Total Espécies',
    'value' => 156,
    'icon' => 'fas fa-leaf',
    'subtext' => 'No ecossistema',
    'cssClass' => 'custom-class', // opcional
]) ?>
```

#### `TimelineCard` - Item de Timeline/Lista
```php
<?= TimelineCard::widget([
    'title' => 'Rosa Canina',
    'subtitle' => 'Rosa canina subsp. canina',
    'badge' => 'Publicada',
    'badgeClass' => 'is-published',
    'meta' => [
        'Autor' => 'João Silva',
        'Data' => '13/04/2026',
        'Confiança' => '92%',
    ],
    'actions' => [
        ['label' => 'Ver detalhes', 'url' => ['observation/view', 'id' => 1], 'icon' => 'fas fa-eye'],
        ['label' => 'Editar', 'url' => ['observation/update', 'id' => 1], 'icon' => 'fas fa-edit'],
    ],
    'content' => 'Observação adicional...',
    'cssClass' => 'custom-class',
]) ?>
```

#### `EmptyState` - Estado Vazio
```php
<?= EmptyState::widget([
    'icon' => 'fas fa-search',
    'title' => 'Nenhuma espécie encontrada',
    'message' => 'Tente ajustar sua pesquisa ou filtros.',
    'actions' => [
        ['label' => 'Voltar', 'url' => ['species/index'], 'icon' => 'fas fa-redo', 'class' => 'btn-outline-brand'],
        ['label' => 'Nova Espécie', 'url' => ['species/create'], 'icon' => 'fas fa-plus', 'class' => 'btn-brand'],
    ],
    'cssClass' => 'custom-class',
]) ?>
```

#### `FilterChips` - Filtros Acionáveis
```php
<?= FilterChips::widget([
    'chips' => [
        ['label' => 'Todas', 'url' => ['index'], 'active' => true, 'icon' => 'fas fa-list'],
        ['label' => 'Publicadas', 'url' => ['index', 'status' => 'published'], 'icon' => 'fas fa-check'],
        ['label' => 'Pendentes', 'url' => ['index', 'status' => 'pending'], 'icon' => 'fas fa-clock'],
        ['label' => 'Falhadas', 'url' => ['index', 'status' => 'failed'], 'icon' => 'fas fa-times'],
    ],
    'cssClass' => 'custom-class',
]) ?>
```

#### `GalleryGrid` - Grade de Imagens com Lightbox
```php
<?= GalleryGrid::widget([
    'images' => [
        ['url' => '/images/photo1.jpg', 'thumb' => '/images/thumb1.jpg', 'alt' => 'Photo 1', 'title' => 'Imagem 1'],
        ['url' => '/images/photo2.jpg', 'thumb' => '/images/thumb2.jpg', 'alt' => 'Photo 2', 'title' => 'Imagem 2'],
    ],
    'galleryName' => 'observation-images',
    'enableLightbox' => true,
    'cssClass' => 'custom-class',
]) ?>
```

#### `FileUploadField` - Upload com Drag-and-Drop
```php
<?= FileUploadField::widget([
    'name' => 'image',
    'label' => 'Selecionar imagem',
    'subtext' => 'PNG, JPG até 10MB',
    'accept' => 'image/*',
    'maxFiles' => 1,
    'showPreview' => true,
    'icon' => 'fas fa-cloud-upload-alt',
    'cssClass' => 'custom-class',
]) ?>

// Em formulário:
<?= $form->field($model, 'image')->widget(FileUploadField::class, [
    'accept' => 'image/*',
    'showPreview' => true,
]) ?>
```

### 3. **Sistema de Notificações (SweetAlert2)**

#### Notificação de Sucesso
```php
// JavaScript
Notification.success('Operação realizada com sucesso!');
Notification.success('Sucesso!', { timer: 3000, position: 'bottom-end' });
```

#### Notificação de Erro
```php
Notification.error('Ocorreu um erro ao processar a solicitação.');
```

#### Notificação de Aviso
```php
Notification.warning('Atenção: Esta ação é irreversível.');
```

#### Notificação de Informação
```php
Notification.info('Nova atualização disponível.');
```

#### Confirmação de Ação
```php
Notification.confirm(
    'Tem a certeza que deseja eliminar este item?',
    function() {
        // Executar ação
        document.getElementById('delete-form').submit();
    },
    'Confirmar eliminação'
);
```

#### Carregamento
```php
Notification.loading('Processando...');
// ... fazer algo ...
Notification.close();
```

### 4. **Utilitários de UI (UIHelpers)**

#### Gerenciar Estado de Carregamento em Botões
```javascript
const btn = document.querySelector('.btn-action');
UIHelpers.setButtonLoading(btn); // Ativa estado de carregamento
UIHelpers.unsetButtonLoading(btn); // Remove estado
```

#### Upload de Arquivo com Drag-and-Drop
```javascript
const zone = document.querySelector('[data-file-upload]');
const input = zone.querySelector('input[type="file"]');

UIHelpers.setupFileUpload(zone, input, (files) => {
    console.log('Arquivos selecionados:', files);
});

// Alternativamente, auto-inicializa com data-file-upload
```

#### Inicializar Galeria Lightbox
```javascript
UIHelpers.initGallery(); // Usa FSLightbox
```

#### Formatação de Números
```javascript
UIHelpers.formatNumber(1234567); // "1.234.567"
```

#### Formatação de Datas
```javascript
UIHelpers.formatDate(new Date()); // "13/04/2026"
UIHelpers.formatDate(new Date(), 'long'); // "13 de abril de 2026"
UIHelpers.formatDate(new Date(), 'datetime'); // "13/04/2026 14:30"
```

#### Copiar para Clipboard
```javascript
UIHelpers.copyToClipboard('Texto para copiar');
```

#### Obter Geolocalização
```javascript
UIHelpers.getGeolocation(
    function(position) {
        console.log('Latitude:', position.lat, 'Longitude:', position.lng);
        // Preencher campos de localização
        document.querySelector('[name="latitude"]').value = position.lat;
        document.querySelector('[name="longitude"]').value = position.lng;
    },
    function(error) {
        console.error('Erro:', error);
    }
);
```

#### Validar Tipo de Arquivo
```javascript
const file = document.querySelector('input[type="file"]').files[0];
if (UIHelpers.isValidFileType(file, ['image/jpeg', 'image/png'])) {
    console.log('Arquivo válido');
}
```

#### Auto-redimensionar Textarea
```html
<textarea data-auto-resize placeholder="Escrever mensagem..."></textarea>
<!-- Auto-inicializa -->
```

### 5. **Melhorias de Acessibilidade**

#### CSS (`accessibility.css`)
- Skip-to-main link
- Focus states visíveis
- Screen reader only content (`.sr-only`)
- ARIA live regions
- Status pills com cores acessíveis
- Suporte a `prefers-reduced-motion`
- Estilos de impressão

#### HTML Atualizado
- `role="navigation"`, `role="main"`, `role="search"`, `role="button"`
- `aria-label` em ícones
- `aria-hidden="true"` para ícones decorativos
- `title` atributos em links
- `lang="la"` para nomes científicos
- Meta CSRF token melhorado

#### Navegação Responsiva
- Ícones visibility em mobile (`.d-none.d-lg-inline`)
- Contraste melhorado
- Hover states em todos os elementos interativos

### 6. **Componentes CSS Reutilizáveis**

#### Componentes disponíveis em `components.css`
- `.stat-card` - Cards de estatísticas
- `.timeline-item` - Items de timeline
- `.empty-state` - Estados vazios
- `.filter-chips` - Chips de filtro
- `.gallery-grid` - Grade de galerias
- `.file-upload-zone` - Zona de upload
- `.skeleton` - Skeleton loaders
- Variantes de botões (`.btn-ghost`, `.btn-icon`, `.btn-loading`)
- Badges e status pills
- Badges de sucesso/aviso/erro

#### Sistema de Cores Unificado
Todas as cores usam CSS variables:
```css
--gf-brand: #3e7a57
--gf-primary: #7fc084
--gf-accent: #9ccc65
--gf-bg: #f4f7f2
--gf-surface: #ffffff
--gf-surface-soft: #f5f5f5
--gf-text: #212121
--gf-muted: #757575
--gf-border: rgba(62, 122, 87, 0.14)
--gf-shadow / --gf-shadow-strong
```

---

## 🔧 Como Usar as Melhorias

### Atualizar View Existente

1. **Importar Widgets Necessários** no topo do arquivo `.php`:
```php
use app\components\StatCard;
use app\components\TimelineCard;
use app\components\EmptyState;
use app\components\FilterChips;
use app\components\GalleryGrid;
use app\components\FileUploadField;
```

2. **Substituir Cards Antigos** por novos widgets:
```php
// Antigo
<article class="hero-stat-card">
    <span>Espécies</span>
    <strong>156</strong>
</article>

// Novo
<?= StatCard::widget(['label' => 'Espécies', 'value' => 156, 'icon' => 'fas fa-leaf']) ?>
```

3. **Adicionar Ícones FontAwesome**:
```php
// Em navegação
<a href="<?= Url::to(['species/index']) ?>">
    <i class="fas fa-leaf" aria-hidden="true"></i>
    <span class="d-none d-lg-inline">Espécies</span>
</a>
```

4. **Usar Notificações em Controllers/Views**:
```php
// Em controller
Yii::$app->session->setFlash('success', 'Espécie criada com sucesso!');

// Em view (automático via JavaScript)
```

### Estrutura de Arquivo

```
web/
├── css/
│   ├── site.css (estilos base melhorados)
│   ├── components.css (componentes reutilizáveis)
│   └── accessibility.css (acessibilidade)
├── js/
│   ├── notifications.js (SweetAlert2 wrapper)
│   └── ui-helpers.js (utilitários de UI)
components/
├── StatCard.php
├── TimelineCard.php
├── EmptyState.php
├── FilterChips.php
├── GalleryGrid.php
├── FileUploadField.php
└── EnhancedFormField.php
views/
└── layouts/
    └── main.php (atualizado com acessibilidade e ícones)
```

---

## 📱 Responsividade

Todos os componentes são responsivos:
- **Desktop**: Layout completo com toda a informação
- **Tablet** (< 992px): Navegação ajustada, layout em coluna
- **Mobile** (< 768px): Apenas ícones na nav, cards empilhados

---

## 🎨 Próximas Melhorias Sugeridas

1. **Dark Mode** - CSS variables já suportam (implementação futura)
2. **Storybook** - Documentação interativa de componentes
3. **Animações** - Microinterações para feedback visual
4. **Temas** - Suporte a múltiplas paletas de cores

---

## 📖 Recursos Úteis

- [FontAwesome 6.5.1 Icons](https://fontawesome.com/icons)
- [SweetAlert2 Documentation](https://sweetalert2.github.io/)
- [FSLightbox Documentation](https://fslightbox.com/)
- [Yii2 Widgets Documentation](https://www.yiiframework.com/doc/guide/2.0/en/structure-widgets)
- [ARIA Authoring Practices](https://www.w3.org/WAI/ARIA/apg/)

---

**Versão**: 1.0  
**Data**: 13/04/2026  
**Status**: Todas as funcionalidades preservadas ✅
