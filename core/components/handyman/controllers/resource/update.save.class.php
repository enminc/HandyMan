<?php
class hmcResourceUpdateSave extends hmController {

    protected $cache = false;
    protected $templateFile = 'resource/update.save';
    protected $viewType = hmController::VIEW_DIALOG;

    /** @var modResource $resource */
    public $resource;
    /** @var modTemplate $template */
    public $template;

    public function getPageTitle() {
        return 'Saving: '.$this->resource->get('pagetitle');
    }
    public function setup() {
        if (empty($_REQUEST['id'])) {
            return 'No valid resource id passed.';
        }
        $this->resource = $this->modx->getObject('modResource',intval($_REQUEST['id']));
        if (empty($this->resource)) {
            return 'Resource not found.';
        }
        $this->resource->set('editedby',$this->modx->user->get('id'));
        $this->resource->set('editedon',strftime('%Y-%m-%d %H:%M:%S'));
        $this->template = $this->resource->getOne('Template');
        return true;
    }

    /**
     * Process this page, load the resource, and present its values
     * @return void
     */
    public function process() {
        $data = $this->processInput($_REQUEST);

        /* @var modProcessorResponse $response */
        $response = $this->modx->runProcessor('resource/update',$data);

        if (!$response->isError()) {
            $tempRes = $response->getObject();
            $this->resource = $this->modx->getObject('modResource',$tempRes['id']);
            $this->setPlaceholders(
                array(
                    'message' => 'Resource updated.',
                    'resid' => $this->resource->get('id'),
                    'ctx' => $this->resource->get('context_key'),
                )
            );
        } else {
            $error = $response->getAllErrors();
            $this->setPlaceholder('message','Something went wrong updating the Resource: '.implode(', ',$error));
        }
    }

    /**
     * Process POSTed data for richtext.
     * @param array $data
     * @return array
     */
    public function processInput($data) {
        foreach ($data as $key => $value) {
            /* If richtext, parse using textile */
            if (substr($key,-9) == '-richtext') {
                $this->hm->modx->getService('t2h','textile',$this->hm->config['corePath'].'classes/textile/');
                $data[substr($key,0,-9)] = $this->hm->modx->t2h->TextileThis($value);
                unset ($data[$key]);
            }
        }
        /* Makes sure TVs are processed in the update processor. */
        $data['tvs'] = true;
        return $data;
    }
}