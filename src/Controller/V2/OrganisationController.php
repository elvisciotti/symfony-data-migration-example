<?php

namespace App\Controller\V2;

use App\Entity\V2\Organisation;
use App\Repository\OrganisationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/organisations")
 * @SWG\Tag(name="Organisation")
 */
class OrganisationController extends AbstractController
{
    /**
     * @var SerializerInterface
     */
    private $serialiser;

    /**
     * @var OrganisationRepository
     */
    private $organisationRepository;

    /**
     * OrganisationController constructor.
     * @param SerializerInterface $serialiser
     * @param OrganisationRepository $organisationRepository
     */
    public function __construct(SerializerInterface $serialiser, OrganisationRepository $organisationRepository)
    {
        $this->serialiser = $serialiser;
        $this->organisationRepository = $organisationRepository;
    }

    /**
     * @param Organisation $organisation
     *
     * @ParamConverter("organisation", class="App\Entity\V2\Organisation")
     *
     * @Route(
     *     path="/{id}",
     *     methods={"GET"},
     *     requirements={"id"="\d+"}
     * )
     *
     * @SWG\Parameter(
     *       name="groups",
     *       description="response groups",
     *       required=false,
     *       in="query",
     *       type="array",
     *       @SWG\Items(
     *          type="string",
     *          enum={"organisation", "organisation-id" }
     *       )
     * ),
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns organisation object by id",
     *     @Model(
     *          type=\App\Entity\V2\Organisation::class
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function getById(Organisation $organisation, Request $request)
    {
        $groups = $request->query->get('groups', ['organisation', 'organisation-id']);

        // this can be moved to a helper, or event response listener
        // Keep it here to keep thing simple to evaluate
        $content = $this->serialiser->serialize($organisation, 'json', [
            AbstractNormalizer::GROUPS => $groups,
        ]);

        $headers = [
            // send e-tag for the same content
            'e-tag' => 'organisation-' . sha1($content)
        ];

        return new JsonResponse($content, 200, $headers, true);
    }

    /**
     * @Route(
     *     path="/",
     *     methods={"GET"}
     * )
     *
     * @SWG\Parameter(
     *       name="groups",
     *       description="response groups",
     *       required=false,
     *       in="query",
     *       type="array",
     *       @SWG\Items(
     *          type="string",
     *          enum={"organisation", "organisation-id" }
     *       )
     * ),
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns organisations",
     *     @SWG\Items(
     *          ref=@Model(type=\App\Entity\V2\Organisation::class)
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function getAll(Request $request)
    {
        $groups = $request->query->get('groups', ['organisation', 'organisation-id']);

        $organisations = $this->organisationRepository->findAll();

        // this can be moved to a helper, or event response listener
        // Keep it here to keep thing simple to evaluate
        $content = $this->serialiser->serialize($organisations, 'json', [
            AbstractNormalizer::GROUPS => $groups,
        ]);

        $headers = [
            // send e-tag for the same content
            'e-tag' => 'organisations-' . sha1($content)
        ];

        return new JsonResponse($content, 200, $headers, true);
    }

    /**
     * @return JsonResponse
     *
     * @Route(
     *     path="",
     *     methods={"POST"}
     * )
     * @SWG\Parameter(
     *     name=" params",
     *     in="body",
     *     description="new organisation object",
     *     @Model(type=App\Entity\V2\Organisation::class, groups={"organisation"})
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Creates new organisation",
     *     @Model(type=App\Entity\V2\Organisation::class)
     * )
     */
    public function create(): JsonResponse
    {
        throw new \RuntimeException('not implemented');
    }

    /**
     * @return JsonResponse
     *
     * @Route(
     *     path="/{id}",
     *     methods={"PUT"},
     *     requirements={"id"="\d+"}
     * )
     *
     * @SWG\Parameter(
     *     name=" params",
     *     in="body",
     *     description="new organisation object",
     *     @Model(type=App\Entity\V2\Organisation::class, groups={"organisation"})
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Creates new organisation",
     *     @Model(type=App\Entity\V2\Organisation::class)
     * )
     */
    public function update(Organisation $organisation): JsonResponse
    {
        throw new \RuntimeException('not implemented');
    }

    /**
     * @Route(
     *     path="/{id}",
     *     methods={"DELETE"},
     *     requirements={"id"="\d+"}
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Delete organisation by id"
     * )
     */
    public function delete(Organisation $organisation): JsonResponse
    {
        throw new \RuntimeException('not implemented');
    }
}
