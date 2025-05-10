<?php

namespace T3\Vici\Repository;

use T3\Vici\Model\GenericViciModel;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<GenericViciModel>
 */
class ViciFrontendRepository extends Repository
{
    public function __construct()
    {
        // Empty on purpose
    }

    /**
     * @param class-string<GenericViciModel> $objectType
     */
    public function setObjectType(string $objectType): void
    {
        $this->objectType = $objectType;
    }
}
