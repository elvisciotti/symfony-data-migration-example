<?php

namespace App\Controller\V2;

use App\Entity\V2\Contact;
use App\Repository\ContactRepository;
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
 * @Route("/contacts")
 * @SWG\Tag(name="Contact")
 */
class ContactController extends AbstractController
{
    /**
     * @var SerializerInterface
     */
    private $serialiser;

    /**
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * ContactController constructor.
     * @param SerializerInterface $serialiser
     * @param ContactRepository $contactRepository
     */
    public function __construct(SerializerInterface $serialiser, ContactRepository $contactRepository)
    {
        $this->serialiser = $serialiser;
        $this->contactRepository = $contactRepository;
    }


    /**
     * @param Contact $contact
     *
     * @ParamConverter("contact", class="App\Entity\V2\Contact")
     *
     * @Route(
     *     path="/{id}",
     *     methods={"GET"},
     *     requirements={"id"="\d+"}
     * )
     *
     *  @SWG\Parameter(
     *       name="groups",
     *       description="response groups",
     *       required=false,
     *       in="query",
     *       type="array",
     *       @SWG\Items(
     *          type="string",
     *          enum={"contact", "contact-id", "contact-employments", "employment", "employment-organisation", "organisation" }
     *       )
     * ),
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns contact object by id",
     *     @Model(
     *          type=\App\Entity\V2\Contact::class
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function getById(Contact $contact, Request $request)
    {
        $groups = $request->query->get('groups', ['contact', 'contact-id']);

        // this can be moved to a helper, or event response listener
        // Keep it here to keep thing simple to evaluate
        $content = $this->serialiser->serialize($contact, 'json', [
            AbstractNormalizer::GROUPS => $groups,
        ]);

        $headers = [
            // send e-tag for the same content
            'e-tag' => 'contact-' . sha1($content)
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
     *       description="response groups e.g. &groups[]=A&groups[]=b ...",
     *       required=false,
     *       in="query",
     *       type="array",
     *       @SWG\Items(
     *          type="string",
     *          enum={"contact", "contact-id", "contact-employments", "employment", "employment-organisation", "organisation" }
     *       )
     * ),
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns contacts",
     *     @SWG\Items(
     *          ref=@Model(type=\App\Entity\V2\Contact::class)
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function getAll(Request $request)
    {
        $groups = $request->query->get('groups', ['contact', 'contact-id']);

        $contacts = $this->contactRepository->findAll();

        // this can be moved to a helper, or event response listener
        // Keep it here to keep thing simple to evaluate
        $content = $this->serialiser->serialize($contacts, 'json', [
            AbstractNormalizer::GROUPS => $groups,
        ]);

        $headers = [
            // send e-tag for the same content
            'e-tag' => 'contacts-' . sha1($content)
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
     *     description="new contact object",
     *     @Model(type=App\Entity\V2\Contact::class, groups={"contact"})
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Creates new contact",
     *     @Model(type=App\Entity\V2\Contact::class)
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
     *     description="new contact object",
     *     @Model(type=App\Entity\V2\Contact::class, groups={"contact"})
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Creates new contact",
     *     @Model(type=App\Entity\V2\Contact::class)
     * )
     */
    public function update(Contact $contact): JsonResponse
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
     *     description="Delete contact by id"
     * )
     */
    public function delete(Contact $contact): JsonResponse
    {
        throw new \RuntimeException('not implemented');
    }
}
