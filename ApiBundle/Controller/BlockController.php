<?php

namespace OpenOrchestra\ApiBundle\Controller;

use OpenOrchestra\ApiBundle\Facade\FacadeInterface;
use OpenOrchestra\ApiBundle\Controller\Annotation as Api;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenOrchestra\ModelInterface\Model\NodeInterface;
use OpenOrchestra\ModelBundle\Document\Block;

/**
 * Class BlockController
 *
 * @Config\Route("block")
 */
class BlockController extends BaseController
{
    /**
     * @param Request $request
     * @param string $nodeId
     *
     * @Config\Route("/list/{language}", name="open_orchestra_api_block_list")
     * @Config\Method({"GET"})
     * @Api\Serialize()
     *
     * @Config\Security("has_role('ROLE_ACCESS_TREE_NODE')")
     *
     * @return FacadeInterface
     */
    public function listBlockAction($language)
    {
        $currentSiteId = $this->get('open_orchestra_backoffice.context_manager')->getCurrentSiteId();
        $currentSite = $this->get('open_orchestra_model.repository.site')->findOneBySiteId($currentSiteId);

        $blocks = array();
        if ($currentSite) {
            $blocks = $currentSite->getBlocks();
            if (count($blocks) == 0) {
                $blocks = $this->getParameter('open_orchestra.blocks');
            }
        }
        foreach($blocks as $key => $block){
            $blocks[$key] = new Block();
            $blocks[$key]->setComponent($block);
        }

        $node = $this->get('open_orchestra_model.repository.node')
        ->findOneByNodeIdAndLanguageAndSiteIdAndLastVersion(NodeInterface::TRANSVERSE_NODE_ID, $language);

        return $this->get('open_orchestra_api.transformer_manager')->get('block_collection')->transform($node->getBlocks(), $blocks, $node->getNodeId());
    }

}