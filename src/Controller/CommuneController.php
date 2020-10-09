<?php

namespace App\Controller;

use App\Entity\Commune;
use App\Entity\Media;
use App\Repository\CommuneRepository;
use App\Repository\MediaRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

class CommuneController extends AbstractController
{
    /**
     * @Route("commune", name="commune", methods={"GET"})
     * @param Request $request
     * @param CommuneRepository $communeRepository
     * @return JsonResponse
     * @OA\Tag(name="Commune")
     * @OA\Response(
     *     response="200",
     *     description="Returns the communes in database",
     *     @OA\JsonContent(
     *      type="array",
     *      @OA\Items(ref=@Model(type=Commune::class, groups={"full"})),
     *     )
     * )
     * @OA\Parameter(
     *     name="population",
     *     in="query",
     *     description="The population of a commune",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Parameter(
     *     name="Nom",
     *     in="query",
     *     description="The name of a commune",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="code",
     *     in="query",
     *     description="The Code of a commune",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="codeRegion",
     *     in="query",
     *     description="The code region of a commune",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="codeDepartement",
     *     in="query",
     *     description="The code departement of a commune",
     *     @OA\Schema(type="string")
     * )
     */
    public function getCommune(Request $request, CommuneRepository $communeRepository)
    {
        $filter = [];
        $em = $this->getDoctrine()->getManager();
        $metaData = $em->getClassMetadata(Commune::class)->getFieldNames();
        foreach ($metaData as $value) {
            if ($request->query->get($value)) {
                $filter[$value] = $request->query->get($value);
            }
        }

        $response = new JsonResponse();
        $response->setStatusCode(Response::HTTP_OK);
        $response->setContent($this->serializeCommune($communeRepository->findBy($filter)));
        return $response;
    }

    /**
     * @Route("/front/commune",name="frontCommune",methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function getCommuneFront(Request $request, CommuneRepository $communeRepository){
        $filter = [];
        $em = $this->getDoctrine()->getManager();
        $metaData = $em->getClassMetadata(Commune::class)->getFieldNames();
        foreach ($metaData as $value) {
            if ($request->query->get($value)) {
                $filter[$value] = $request->query->get($value);
            }
        }
        return $this->render('commune/index.html.twig', [
            'controller_name' => 'PresentationController',
            'communes' => $communeRepository->findBy($filter)
        ]);
    }
    /**
     * @Route("api/admin/commune", name="addCommune", methods={"PUT"})
     * @OA\Response(
     *     response="200",
     *     description="Create sucessfull commune",
     *     @OA\JsonContent(
     *      type="string"
     *     )
     * )
     * @OA\Parameter(
     *     name="population",
     *     in="query",
     *     description="The population of a commune",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Parameter(
     *     name="Nom",
     *     in="query",
     *     description="The name of a commune",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="code",
     *     in="query",
     *     description="The Code of a commune",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="codeRegion",
     *     in="query",
     *     description="The code region of a commune",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="codeDepartement",
     *     in="query",
     *     description="The code departement of a commune",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     description="Authorization",
     *     @OA\Schema(type="string")
     * )
     *
     * @param Request $request
     * @return Response
     * @Security(name="Bearer")
     * @OA\Tag(name="Commune")
     */
    public function createCommune(Request $request) {
        $entityManager = $this->getDoctrine()->getManager();
        $commune = new Commune();
        $datas = json_decode($request->getContent(),true);
        $commune->setCode($datas['code'])
            ->setCodeDepartement($datas['codeDepartement'])
            ->setCodeRegion($datas['codeRegion'])
            ->setCodesPostaux($datas['codePostaux'])
            ->setNom($datas['nom'])
            ->setPopulation($datas['population']);
        if ($datas['medias']) {
            $arrayMedia = $datas['medias'];
            foreach ($arrayMedia as $dataMedia) {
                $contentMedia = new Media();
                $contentMedia->setCommune($commune)
                    ->setUrl($dataMedia['url']);
                $entityManager->persist($contentMedia);
            }
        }
        $entityManager->persist($commune);
        $entityManager->flush();
        $response = new Response();
        $response->setStatusCode(Response::HTTP_OK);
        $response->setContent("Commune created at id : " . $commune->getId());
        return $response;
    }

    /**
     * @Route ("api/admin/commune", name="deleteCommune", methods={"DELETE"})
     * @OA\Response(
     *     response="200",
     *     description="Create sucessfull commune",
     *     @OA\JsonContent(
     *      type="string"
     *     )
     * )
     * @OA\Response(
     *     response="400",
     *     description="Bad request, need to implement commune_id",
     *     @OA\JsonContent(
     *      type="string"
     *     )
     * )
     *
     * @OA\Parameter(
     *     name="commune_id",
     *     in="query",
     *     description="The commune_id of a commune",
     *     required=true,
     *     @OA\Schema(type="integer")
     * )
     * @OA\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     description="Authorization",
     *     @OA\Schema(type="string")
     * )
     *
     * @param Request $request
     * @param CommuneRepository $communeRepository
     * @return Response
     * @Security(name="Bearer")
     * @OA\Tag(name="Commune")
     */
    public function deleteCommune(Request $request, CommuneRepository $communeRepository) {
        $entityManager = $this->getDoctrine()->getManager();
        $data = json_decode(
            $request->getContent(),
            true
        );
        $response = new Response();
        if (isset($data['commune_id'])) {
            $commune = $communeRepository->find($data['commune_id']);
            if ($commune === null) {
                $response->setContent("Cette commune n'existe pas");
                $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            } else {
                $entityManager->remove($commune);
                $entityManager->flush();
                $response->setContent("Suppression de la commune");
                $response->setStatusCode(Response::HTTP_OK);
            }
        } else {
            $response->setContent("Mauvais format de la requête");
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }
        return $response;
    }

    /**
     * @Route ("api/admin/commune", name="updateCommune", methods={"PATCH"})
     * @OA\Response(
     *     response="200",
     *     description="Create sucessfull commune",
     *     @OA\JsonContent(
     *      type="string"
     *     )
     * )
     * @OA\Response(
     *     response="400",
     *     description="Bad request, need to implement commune_id minimum and other fields you wan't to change",
     *     @OA\JsonContent(
     *      type="string"
     *     )
     * )
     * @OA\Parameter(
     *     name="commune_id",
     *     in="query",
     *     required=true,
     *     description="The commune_id of a commune",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     description="Authorization",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="population",
     *     in="query",
     *     description="The population of a commune",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Parameter(
     *     name="Nom",
     *     in="query",
     *     description="The name of a commune",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="code",
     *     in="query",
     *     description="The Code of a commune",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="codeRegion",
     *     in="query",
     *     description="The code region of a commune",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="codeDepartement",
     *     in="query",
     *     description="The code departement of a commune",
     *     @OA\Schema(type="string")
     * )
     * @param Request $request
     * @param CommuneRepository $communeRepository
     * @param MediaRepository $mediaRepository
     * @return Response
     * @Security(name="Bearer")
     * @OA\Tag(name="Commune")
     */
    public function updateCommune(Request $request, CommuneRepository $communeRepository,MediaRepository $mediaRepository) {
        $entityManager = $this->getDoctrine()->getManager();
        $data = json_decode(
            $request->getContent(),
            true
        );
        $response = new Response();
        if ($data['commune_id']) {
            $commune = $communeRepository->findOneBy(['id' => $data['commune_id']]);
            $newCommune = $commune;
            isset($data["nom"]) && $newCommune->setNom($data['nom']);
            isset($data["code"]) && $newCommune->setCode($data['code']);
            isset($data["codeDepartement"]) && $newCommune->setcodeDepartement($data['codeDepartement']);
            isset($data["codeRegion"]) && $newCommune->setcodeRegion($data['codeRegion']);
            isset($data["population"]) && $newCommune->setpopulation($data['population']);
            isset($data["codesPostaux"]) && $newCommune->setcodesPostaux($data['codesPostaux']);
            if ($data["medias"]) {
                foreach ($data["medias"] as $media) {
                    $contentMedia = $mediaRepository->findOneBy(['id' => $media['id']]);
                    $contentMedia->setUrl($media['url']);
                    $entityManager->persist($contentMedia);
                }
            }

            $entityManager->persist($commune);
            $entityManager->flush();
            $response->setContent("Mise à jours de la commune à l'id : " . $commune->getId());
            $response->setStatusCode(Response::HTTP_OK);
        }else {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }
        return $response;
    }

    private function serializeCommune($objet){
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getSlug();
            },
        ];
        $normalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);
        $serializer = new Serializer([$normalizer], [new JsonEncoder()]);

        return $serializer->serialize($objet, 'json');
    }
}
