<?php

namespace Drupal\xtools\Taxonomy;

use Drupal\Core\Url;

/**
 * Configure text display settings for this the hello world page.
 */
class TaxonomyTree {

    /** @var  \Drupal\Core\Entity\EntityTypeManagerInterface\EntityTypeManagerInterface */
    private $entityManager;

    /**
     * CatalogManager constructor.
     * @param $entityManager
     */
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns renderable array of taxonomy terms from Categories vocabulary in
     * hierarchical structure ready to be rendered as html list.
     *
     * @param $vocabulary
     *   The ID of vocabulary.
     * @param int $parent
     *   The ID of the parent taxonomy term.
     *
     * @param int $max_depth
     *   The max depth up to which to look up children.
     * @return array
     */
    function tree($vocabulary, $parent = 0, $max_depth = NULL) {

        // Load terms
        $tree = $this->entityManager
            ->getStorage('taxonomy_term')
            ->loadTree($vocabulary, $parent, $max_depth);

        // Make sure there are terms to work with.
        if (empty($tree)) {
            return [];
        }

        // Sort tree by depth so we can easily find out the deepest level
        uasort($tree, function($a, $b) {
            // Change objects to array
            return \Drupal\Component\Utility\SortArray::sortByKeyInt((array) $a, (array) $b, 'depth');
        });

        // Get the value of the deepest term
        $deepest = end($tree);
        $deepest = $deepest->depth;

        // Create a structured array
        $list = [
            $parent => [
                'children' => [],
                'depth' => -1
            ]
        ];

        foreach ($tree as $term) {
            $list[$term->tid] = (array) $term;
        }


        for ($i = $deepest; $i >= 0; $i--) {

            foreach ($list as $term) {

                if(!empty($term['tid'])) {
                    $term['url'] = Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $term['tid']])->toString();
                    $term['label'] = $term['name'];
                }

                if ($term['depth'] == $i) {

                    foreach ($term['parents'] as $pid) {

                        $list[$pid]['children'][$term['tid']] = $term;
                    }

                    unset($list[$term['tid']]);
                }
            }
        }

      $links = $list[$parent]['children'];

      // Sort tree by depth so we can easily find out the deepest level
      uasort($links, function($a, $b) {
        // Change objects to array
        return \Drupal\Component\Utility\SortArray::sortByKeyString((array) $a, (array) $b, 'label');
      });

      dump($tree);
      return $links;
    }
}
