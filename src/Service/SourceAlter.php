<?php

use Drupal\domain\DomainNegotiatorInterface;

/**
 *
 */
class SourceAlter {

  /**
   * Undocumented variable.
   *
   * @var [type]
   */
  protected static $entity = NULL;

  /**
   * Undocumented variable.
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
      $this->entity = $options['entity'];
      if ($this->entity->hasField('field_domain_access') &&
      $this->domainNegotiator->getActiveId() != $source->id()) {
        foreach ($this->entity->field_domain_access as $item) {
          if ($item->target_id == $this->domainNegotiator->getActiveId()) {
            $source = NULL;
            $this->altered = TRUE;
            return;
          }
        }
      }
    }
  }

  /**
   * Undocumented function.
   *
   * @param array $attachments
   *
   * @return void
   */
  public function alterCanonical(array &$attachments) {
    if ($this->altered != NULL && !empty($attachments['#attached']['html_head'])) {
      foreach ($attachments['#attached']['html_head'] as $key => $entry) {
        if ($entry[1] == 'canonical_url') {
          $host = \Drupal::request()->getHost();
          $source = \Drupal::entityTypeManager()->getStorage('domain')->loadDefaultDomain();
          $entry[0]['#attributes']['href'] = str_replace($host, $source->getHostname(), $entry[0]['#attributes']['href']);
          $attachments['#attached']['html_head'][$key] = $entry;
        }
      }
    }
  }

}
