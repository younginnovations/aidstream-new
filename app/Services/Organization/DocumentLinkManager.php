<?php
namespace App\Services\Organization;

use App\Core\Version;
use App;
use App\Models\Organization\OrganizationData;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Logging\Log;

class DocumentLinkManager
{

    protected $repo;
    /**
     * @var Version
     */
    protected $version;
    /**
     * @var Log
     */
    protected $log;
    /**
     * @var Guard
     */
    protected $auth;

    /**
     * @param Version $version
     * @param Log     $log
     * @param Guard   $auth
     */
    function __construct(Version $version, Log $log, Guard $auth)
    {
        $this->repo    = $version->getOrganizationElement()->getDocumentLink()->getRepository();
        $this->version = $version;
        $this->log     = $log;
        $this->auth    = $auth;
    }

    /**
     * @param $input
     * @param $organization
     * @return bool
     */
    public function update(array $input, OrganizationData $organization)
    {
        try {
            $this->repo->update($input, $organization);
            $this->log->info(
                'Document Link Updated',
                ['for' => $organization->document_link]
            );
            $this->log->activity(
                "organization.document_link_updated",
                ['name' => $this->auth->user()->organization->name]
            );

            return true;
        } catch (Exception $exception) {

            $this->log->error(
                sprintf('Document Link could not be updated due to %s', $exception->getMessage()),
                [
                    'OrganizationDocumentLink' => $input,
                    'trace'                    => $exception->getTraceAsString()
                ]
            );
        }

        return false;
    }


    /**
     * write brief description
     * @param $id
     * @return model
     */
    public function getOrganizationData($id)
    {
        return $this->repo->getOrganizationData($id);

    }

    /**
     * write brief description
     * @param $id
     * @return model
     */
    public function getDocumentLinkData($id)
    {
        return $this->repo->getDocumentLinkData($id);

    }


}