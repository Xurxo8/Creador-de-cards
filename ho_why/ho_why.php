<?php
if (!defined('_PS_VERSION_')) {
  exit;
}

class Ho_why extends Module {

  // Propiedades del módulo
  protected $config_form = false;
  protected $_html = ''; // Para mensajes de confirmación o error

  public function __construct()
  {
    $this->name = 'ho_why';
    $this->tab = 'merchandizing';
    $this->version = '1.0.0';
    $this->author = 'Xurxo';
    $this->need_instance = 0;

    $this->bootstrap = true; // Soporte para Bootstrap en el BO
    parent::__construct();

    $this->displayName = $this->l('HoWhy'); // Nombre visible en el BO
    $this->description = $this->l('Razones para comprar en Hardware Online');
    $this->ps_versions_compliancy = ['min' => '1.6', 'max' => '9.0'];
  }

  /**
   * Instalación del módulo
   */
  public function install()
  {
    // Valor por defecto de configuración
    Configuration::updateValue('HO_WHY_LIVE_MODE', false);

    // Registrar hooks y crear tabla de datos
    return parent::install() &&
      $this->registerHook('header') &&
      $this->registerHook('displayBackOfficeHeader') &&
      $this->registerHook('displayHome');
  }

  /**
   * Desinstalación del módulo
   */
  public function uninstall(){
    Configuration::deleteByName('HO_WHY_LIVE_MODE');
    return parent::uninstall();
  }

  /**
   * Genera la página de configuración en el BO
   */
  public function getContent(){
    $cards = $this->readCards();

    // Eliminar card
    if ($idDelete = Tools::getValue('delete')) {
      $cards = array_filter($cards, fn($c) => $c['id'] !== $idDelete);
      $this->saveCards(array_values($cards));
      $this->_html .= $this->displayConfirmation($this->l('Card eliminada correctamente.'));
    }

    // Procesar formulario si se ha enviado
    if (Tools::isSubmit('submitHo_whyModule')) {
      $this->postProcess();
    }

    // Leer todas las cards existentes
    $cards = $this->readCards();

    // Asignar variables a Smarty para plantilla
    $this->context->smarty->assign([
      'module_dir' => $this->_path,
      'cards' => $cards,
      'current' => $this->context->link->getAdminLink('AdminModules', false)
        .'&configure='.$this->name,
    ]);

    // Mostrar mensajes y plantilla
    $output = $this->_html;
    $output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

    // Editar card
    if ($idEdit = Tools::getValue('edit')) {
      foreach ($cards as $card) {
        if ($card['id'] === $idEdit) {
          $output .= $this->renderForm($card);
          return $output;
        }
      }
    }

    // Mostrar formulario vacío para añadir nuevas cards
    $output .= $this->renderForm();

    return $output;
  }

  /**
   * Renderiza el formulario para crear nuevas cards
   */
  protected function renderForm($card = null){
    $helper = new HelperForm();

    $helper->show_toolbar = false;
    $helper->module = $this;
    $helper->default_form_language = $this->context->language->id;

    // Configuración básica del HelperForm
    $helper->identifier = $this->identifier;
    $helper->submit_action = 'submitHo_whyModule';
    $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
      .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
    $helper->token = Tools::getAdminTokenLite('AdminModules');

    // Si no se pasa una card, inicializar valores vacíos
    $fieldsValue = [
      'HO_WHY_ID_CARD' => $card['id'] ?? '',
      'HO_WHY_IMG_NAME' => $card['name'] ?? '',
      'HO_WHY_DESCRIPCION_NAME' => $card['description'] ?? '',
      'HO_WHY_IMG_FILE' => '',
    ];

    $helper->tpl_vars = [
      'fields_value' => [
        'HO_WHY_ID_CARD' => $card['id'] ?? '',
        'HO_WHY_IMG_NAME' => $card['name'] ?? '',
        'HO_WHY_DESCRIPCION_NAME' => $card['description'] ?? '',
        'HO_WHY_IMG_FILE' => '',
      ],
      'languages' => $this->context->controller->getLanguages(),
      'id_language' => $this->context->language->id,
    ];

    $helper->tpl_vars['enctype'] = 'multipart/form-data'; // Para subir imágenes

    return $helper->generateForm([$this->getConfigForm()]);
  }

  /**
   * Estructura del formulario
   */
  protected function getConfigForm()  {
    return [
      'form' => [
        'input' => [
          [
            'type' => 'hidden',
            'name' => 'HO_WHY_ID_CARD',
          ],
          [
            'type' => 'text',
            'name' => 'HO_WHY_IMG_NAME',
            'label' => $this->l('Nombre imagen'),
            'col' => 2
          ],
          [
            'type' => 'text',
            'name' => 'HO_WHY_DESCRIPCION_NAME',
            'label' => $this->l('Texto descripción'),
            'col' => 3
          ],
          [
            'type' => 'file',
            'name' => 'HO_WHY_IMG_FILE',
            'label' => $this->l('Subir imagen'),
          ],
        ],
        'submit' => [
          'title' => $this->l('Guardar'),
        ],
      ],
    ];
  }

  /**
   * Procesa los datos del formulario y guarda la nueva card
   */
  protected function postProcess(){
    $cards = $this->readCards();
    if (!Tools::isSubmit('submitHo_whyModule')) return;

    $errors = [];

    $name = Tools::getValue('HO_WHY_IMG_NAME');
    $description = Tools::getValue('HO_WHY_DESCRIPCION_NAME');

    // Validaciones
    if (!$name || trim($name) === '') $errors[] = $this->l('El campo "Nombre" es obligatorio.');
    if (!$description || trim($description) === '') $errors[] = $this->l('El campo "Descripción" es obligatorio.');

    // Subida de imagen (solo SVG permitido)
    $newImage = null;
    if (isset($_FILES['HO_WHY_IMG_FILE']) && !empty($_FILES['HO_WHY_IMG_FILE']['tmp_name'])) {
      $allowedTypes = ['image/svg+xml'];
      if (!in_array($_FILES['HO_WHY_IMG_FILE']['type'], $allowedTypes)) {
        $errors[] = $this->l('Debes subir una imagen de tipo SVG.');
      } else {
        // Subir imagen
        $uploadDir = _PS_MODULE_DIR_.$this->name.'/views/img/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);

        $fileName = uniqid().'-'.basename($_FILES['HO_WHY_IMG_FILE']['name']);
        $targetFile = $uploadDir.$fileName;

        if (!move_uploaded_file($_FILES['HO_WHY_IMG_FILE']['tmp_name'], $targetFile)) {
          $errors[] = $this->l('Error al subir la imagen.');
        } else {
          $newImage = $fileName;
        }
      }
    }

    // Mostrar errores si los hay
    if (!empty($errors)) {
      foreach ($errors as $error) {
        $this->_html .= $this->displayError($error);
      }
      return;
    }

    // Leer cards existentes, añadir la nueva y guardar
    $cardId = Tools::getValue('HO_WHY_ID_CARD');

    if ($cardId) {
      // Editar card existente
      foreach ($cards as &$card) {
        if ($card['id'] === $cardId) {
          $card['name'] = $name;
          $card['description'] = $description;
          if ($newImage) {
            // Borrar imagen antigua si existe
            if (!empty($card['image']) && file_exists(_PS_MODULE_DIR_.$this->name.'/views/img/'.$card['image'])) {
              unlink(_PS_MODULE_DIR_.$this->name.'/views/img/'.$card['image']);
            }
            $card['image'] = $newImage;
          }
          break;
        }
      }
      unset($card); // Romper referencia
    } else {
      // Crear nueva card
      $cards[] = [
        'id' => uniqid(),
        'name' => $name,
        'description' => $description,
        'image' => $newImage,
      ];
    }

    $this->saveCards($cards);
    $this->_html .= $this->displayConfirmation(
        $cardId ? $this->l('Card editada correctamente.') : $this->l('Card creada correctamente.')
    );
  }

  // ====================== Funciones para JSON ======================

  private function getJsonFilePath(){
    return _PS_MODULE_DIR_.$this->name.'/data/cards.json';
  }

  private function readCards(){
    $file = $this->getJsonFilePath();
    if (!file_exists($file)) return [];
    $json = file_get_contents($file);
    return json_decode($json, true) ?: [];
  }

  private function saveCards(array $cards){
    $file = $this->getJsonFilePath();
    if (!file_exists(dirname($file))) mkdir(dirname($file), 0755, true);
    file_put_contents($file, json_encode($cards, JSON_PRETTY_PRINT));
  }

  // ====================== Hooks ======================

  public function hookDisplayBackOfficeHeader(){
    if (Tools::getValue('configure') == $this->name) {
      $this->context->controller->addJS($this->_path.'views/js/back.js');
      $this->context->controller->addCSS($this->_path.'views/css/back.css');
    }
  }

  public function hookHeader(){
    $this->context->controller->addJS($this->_path.'views/js/front.js');
    $this->context->controller->addCSS($this->_path.'views/css/front.css');

    $this->context->controller->registerStylesheet(
      'module-ho_why-style',
      'modules/'.$this->name.'/views/css/howhy.css',
      ['media' => 'all', 'priority' => 150]
    );
  }

  public function hookDisplayHome(){
    $cards = $this->readCards();
    $this->context->smarty->assign(['reasons' => $cards]);
    return $this->display(__FILE__, 'views/templates/hook/howhy.tpl');
  }
}
