<?php

declare(strict_types=1);

namespace Pollen\WpPost;

use Pollen\Translation\LabelsBag;

/**
 * @see https://developer.wordpress.org/reference/functions/get_post_type_labels/
 */
class WpPostTypeLabelsBag extends LabelsBag
{
    /**
     * @inheritDoc
     */
    public function parse(): void
    {
        $this->set(
            [
                'name'                     => $this->plural(true),
                'singular_name'            => $this->singular(true),
                'add_new'                  => !$this->gender()
                    ? sprintf('Ajouter un %s', $this->singular())
                    : sprintf('Ajouter une %s', $this->singular()),
                'add_new_item'             => !$this->gender()
                    ? sprintf('Ajouter un %s', $this->singular())
                    : sprintf('Ajouter une %s', $this->singular()),
                'edit_item'                => sprintf('Éditer %s', $this->singularDefinite()),
                'new_item'                 => !$this->gender()
                    ? sprintf('Créer un %s', $this->singular())
                    : sprintf('Créer une %s', $this->singular()),
                'view_item'                => !$this->gender()
                    ? sprintf('Voir cet %s', $this->singular())
                    : sprintf('Voir cette %s', $this->singular()),
                'view_items'               => sprintf('Voir ces %s', $this->plural()),
                'search_items'             => !$this->gender()
                    ? sprintf('Rechercher un %s', $this->singular())
                    : sprintf('Rechercher une %s', $this->singular()),
                'not_found'                => !$this->gender()
                    ? sprintf('Aucun %s trouvé', $this->singular(true))
                    : sprintf('Aucune %s trouvée', $this->singular(true)),
                'not_found_in_trash'       => !$this->gender()
                    ? sprintf('Aucun %s dans la corbeille', $this->singular(true))
                    : sprintf('Aucune %s dans la corbeille', $this->singular(true)),
                'parent_item_colon'        => sprintf('%s parent', $this->singular(true)),
                'all_items'                => !$this->gender()
                    ? sprintf('Tous les %s', $this->plural())
                    : sprintf('Toutes les %s', $this->plural()),
                'archives'                 => !$this->gender()
                    ? sprintf('Tous les %s', $this->plural())
                    : sprintf('Toutes les %s', $this->plural()),
                'attributes'               => !$this->gender()
                    ? sprintf('Tous les %s', $this->plural())
                    : sprintf('Toutes les %s', $this->plural()),
                /** @todo * /
                 * 'insert_into_item'      => '',
                 * 'uploaded_to_this_item' => '',
                 * 'featured_image'        => '',
                 * 'set_featured_image'    => '',
                 * 'remove_featured_image' => '',
                 * 'use_featured_image'    => '',
                 * /**/
                'menu_name'                => $this->plural(true),
                /** @todo * /
                'filter_items_list'        => '',
                'filter_by_date'           => '',
                'items_list_navigation'    => '',
                'items_list'               => '',
                'item_published'           => '',
                'item_published_privately' => '',
                'item_reverted_to_draft'   => '',
                'item_scheduled'           => '',
                'item_updated'             => '',
                /**/
                'name_admin_bar'           => $this->singular(true),
            ]
        );
    }
}