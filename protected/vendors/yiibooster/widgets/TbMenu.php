<?php

    /**
     * ## TbMenu class file.
     *
     * @author Christoffer Niska <ChristofferNiska@gmail.com>
     * @copyright Copyright &copy; Christoffer Niska 2012-
     * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php) 
     */
    Yii::import('bootstrap.widgets.TbBaseMenu');

    /**
     * Bootstrap menu.
     *
     * @see <http://twitter.github.com/bootstrap/components.html#navs>
     *
     * @package booster.widgets.navigation
     */
    class TbMenu extends TbBaseMenu {
        // Menu types.

        const TYPE_TABS = 'tabs';
        const TYPE_PILLS = 'pills';
        const TYPE_LIST = 'list';

        /**
         * @var string the menu type.
         *
         * Valid values are 'tabs', 'pills', or 'list'.
         */
        public $type;

        /**
         * @var string|array the scrollspy target or configuration.
         */
        public $scrollspy;

        /**
         * @var boolean indicates whether the menu should appear vertically stacked.
         */
        public $stacked = false;

        /**
         * @var boolean indicates whether dropdowns should be dropups instead.
         */
        public $dropup = false;
        public $submenuHtmlOptions = array("class" => 'submenu');

        /**
         * ### .init()
         *
         * Initializes the widget.
         */
        public function init() {
            parent::init();

            $classes = array('nav');

            $validTypes = array(self::TYPE_TABS, self::TYPE_PILLS, self::TYPE_LIST);

            if (isset($this->type) && in_array($this->type, $validTypes)) {
                $classes[] = 'nav-' . $this->type;
            }

            if ($this->stacked && $this->type !== self::TYPE_LIST) {
                $classes[] = 'nav-stacked';
            }

            if ($this->dropup === true) {
                $classes[] = 'dropup';
            }

            if (isset($this->scrollspy)) {
                $scrollspy = is_string($this->scrollspy) ? array('target' => $this->scrollspy) : $this->scrollspy;
                $this->widget('bootstrap.widgets.TbScrollSpy', $scrollspy);
            }
            if (isset($this->submenu)) {
                $this->renderMenu(array("items" => $this->submenu));
//			$scrollspy = is_string($this->scrollspy) ? array('target' => $this->scrollspy) : $this->scrollspy;
//			$this->widget('bootstrap.widgets.TbScrollSpy', $scrollspy);
            }

            if (!empty($classes)) {
                $classes = implode(' ', $classes);
                if (isset($this->htmlOptions['class'])) {
                    $this->htmlOptions['class'] .= ' ' . $classes;
                } else {
                    $this->htmlOptions['class'] = $classes;
                }
            }
        }

        /**
         * ### .getDividerCssClass()
         *
         * Returns the divider css class.
         *
         * @return string the class name
         */
        public function getDividerCssClass() {
            return (isset($this->type) && $this->type === self::TYPE_LIST) ? 'divider' : 'divider-vertical';
        }

        /**
         * ### .getDropdownCssClass()
         *
         * Returns the dropdown css class.
         *
         * @return string the class name
         */
        public function getDropdownCssClass() {
            return 'dropdown';
        }

        /**
         * ### .isVertical()
         *
         * Returns whether this is a vertical menu.
         *
         * @return boolean the result
         */
        public function isVertical() {
            return isset($this->type) && $this->type === self::TYPE_LIST;
        }

        protected function renderMenu($items) {
            if (count($items)) {
                echo CHtml::openTag('ul', $this->htmlOptions) . "\n";
                $this->renderMenuRecursive($items);
                echo CHtml::closeTag('ul');
            }
        }

        /**
         * Recursively renders the menu items.
         * @param array $items the menu items to be rendered recursively
         */
        protected function renderMenuRecursive($items, $level=0) {
            $n = count($items);

            if ($n > 0) {
//                echo CHtml::openTag('ul', $this->htmlOptions);

                $count = 0;
                foreach ($items as $item) {
                    $count++;

                    if (isset($item['divider'])) {
                        echo '<li class="' . $this->getDividerCssClass() . '"></li>';
                    } else {
                        $options = isset($item['itemOptions']) ? $item['itemOptions'] : array();
                        $classes = array();

                        if ($item['active'] && $this->activeCssClass != '') {
                            $classes[] = $this->activeCssClass;
//                            $classes[] = "icon-double-angle-right";
                        }

                        if ($count === 1 && $this->firstItemCssClass !== null) {
                            $classes[] = $this->firstItemCssClass;
                        }

                        if ($count === $n && $this->lastItemCssClass !== null) {
                            $classes[] = $this->lastItemCssClass;
                        }

                        if ($this->itemCssClass !== null) {
                            $classes[] = $this->itemCssClass;
                        }

                        if (isset($item['items'])) {
                            $classes[] = $this->getDropdownCssClass();
                        }

                        if (isset($item['disabled'])) {
                            $classes[] = 'disabled';
                        }

                        if (!empty($classes)) {
                            $classes = implode(' ', $classes);
                            if (!empty($options['class'])) {
                                $options['class'] .= ' ' . $classes;
                            } else {
                                $options['class'] = $classes;
                            }
                        }

                        echo CHtml::openTag('li', $options);

                        $menu = $this->renderMenuItem($item, $level);

                        if (isset($this->itemTemplate) || isset($item['template'])) {
                            $template = isset($item['template']) ? $item['template'] : $this->itemTemplate;
                            echo strtr($template, array('{menu}' => $menu));
                        } else {
                            echo $menu;
                        }

//                        if (isset($item['items']) && !empty($item['items'])) {
//                            $dropdownOptions = array(
//                                'encodeLabel' => $this->encodeLabel,
//                                'htmlOptions' => isset($item['submenuOptions']) ? $item['submenuOptions'] : $this->submenuHtmlOptions,
//                                'items' => $item['items'],
//                            );
//                            $dropdownOptions['id'] = isset($dropdownOptions['htmlOptions']['id']) ?
//                                    $dropdownOptions['htmlOptions']['id'] : null;
//                            $this->controller->widget('bootstrap.widgets.TbDropdown', $dropdownOptions);
//                        }
                        if (isset($item['items']) && count($item['items'])) {
                            echo "\n" . CHtml::openTag('ul', isset($item['submenuOptions']) ? $item['submenuOptions'] : $this->submenuHtmlOptions) . "\n";
                            $this->renderMenuRecursive($item['items'], $level+1);
                            echo CHtml::closeTag('ul') . "\n";
                        }
                        echo '</li>' . "\n";
                    }
                }

//                echo '</ul>';
            }
        }

        protected function renderMenuItem($item, $level=0) {
            if (isset($item['icon'])) {
                if (strpos($item['icon'], 'icon') === false && strpos($item['icon'], 'fa') === false) {
                    $item['icon'] = 'icon-' . implode(' icon-', explode(' ', $item['icon']));
                }

                $item['label'] = '<i class="' . $item['icon'] . '"></i> ' . $item['label'];
            }

            if (!isset($item['linkOptions'])) {
                $item['linkOptions'] = array();
            }
            $pre = (!empty($item['active']) AND $level>0 ) ? '<i class="icon-double-angle-right"></i>' : "";

            if (isset($item['items']) && !empty($item['items'])) {
                if (empty($item['url'])) {
                    $item['url'] = '#';
                }

                if (isset($item['linkOptions']['class'])) {
                    $item['linkOptions']['class'] .= ' dropdown-toggle';
                } else {
                    $item['linkOptions']['class'] = 'dropdown-toggle';
                }

                $item['linkOptions']['data-toggle'] = 'dropdown';
                $item['label'] .= ' <span class="menu-text"></span><b class="arrow icon-angle-down"></b>';
            }

            if (isset($item['url'])) {
                return CHtml::link($pre.$item['label'], $item['url'], $item['linkOptions']);
            } else {
                return $item['label'];
            }
        }

    }

    