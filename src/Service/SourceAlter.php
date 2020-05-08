<?php

namespace Drupal\domain_stay\Service;

use Drupal\domain\DomainNegotiatorInterface;

/**
 *
 */
class SourceAlter {


  /**
   * The domain negotiator.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator = FALSE;

  /**
   * The entity being processed.
   *
   * @var \Drupal\Core\Entity\ContentEntityBase
   */
  protected static $entity = NULL;

  /**
   * Wether the source has been altered in the current request.
   *
   * @var bool
   */
  protected static $altered = FALSE;

  /**
   * Undocumented function.
   *
   * @param \Drupal\domain\DomainNegotiatorInterface $domainNegotiator
   */
  public function __construct(DomainNegotiatorInterface $domainNegotiator) {
    $this->domainNegotiator = $domainNegotiator;
  }

  /**
   * Sets the source to null if entity is available on domain.
   *
   * @param \Drupal\domain\Entity\Domain|null &$source
   *   A domain object or NULL if not set.
   * @param string $path
   *   The outbound path request.
   * @param array $options
   *   The options for the url, as defined by
   *   \Drupal\Core\PathProcessor\OutboundPathProcessorInterface.
   */
  public function setSource(&$source, $path, array $options) {
    if ($source != NULL && !empty($options['entity'])) {
      static::$entity = $options['entity'];
      if (static::$entity->hasField('field_domain_access') &&
      $this->domainNegotiator->getActiveId() != $source->id()) {
        foreach (static::$entity->field_domain_access as $item) {
          if ($item->target_id == $this->domainNegotiator->getActiveId()) {
            $source = NULL;
            static::$altered = TRUE;
            return;
          }
        }
      }
    }
  }

  /**
   * Adjust the canonical meta tag to match source or default domain.
   *
   * @param array $attachments
   *
   * @return void
   */
  public function alterCanonical(array &$attachments) {
    if (static::$altered != NULL && !empty($attachments['#attached']['html_head'])) {
      foreach ($attachments['#attached']['html_head'] as $key => $entry) {
        if ($entry[1] == 'canonical_url') {
          $sourceDomain = $this->domainNegotiator->getActiveDomain();
          if (static::$entity->hasField('field_domain_source')) {
            $sourceDomain = static::$entity->field_domain_source->entity;
          }
          else {
            $sourceDomain = \Drupal::entityTypeManager()->getStorage('domain')->loadDefaultDomain();
          }
          $entry[0]['#attributes']['href'] = $sourceDomain->getRawPath() . static::$entity->toUrl('canonical')->toString();;
          $attachments['#attached']['html_head'][$key] = $entry;
        }
      }
    }
  }

}
