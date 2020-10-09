<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

class UserController extends AbstractController
{
    /**
     * @Route(path="user/register", name="/register", methods={"POST"})
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return JsonResponse
     * @throws AlreadySubmittedException
     */
    public function registerUser(Request $request, ValidatorInterface $validator, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = new User();
        $datas = json_decode($request->getContent(), true);
        $datas['password'] = $passwordEncoder->encodePassword($user, $datas['password']);
        $formRegister = $this->createForm(RegisterFormType::class, $user);

        $formRegister->submit($datas);
        $violation = $validator->validate($user, null, 'Register');

        if (0 !== count($violation)) {
            foreach ($violation as $error) {
                return new JsonResponse($error->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }

        $entityManager = $this->getDoctrine()->getManager();
        $user->setRoles(["ROLE_USER"]);
        $entityManager->persist($user);
        $entityManager->flush();
        return new JsonResponse('User Created', Response::HTTP_OK);
    }

    /**
     * @Route(path="/api/admin/user/update", name="userUpdate", methods={"PATCH"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return JsonResponse
     * @OA\Tag(name="User")
     */
    public function userUpdate(Request $request, UserRepository $userRepository,UserPasswordEncoderInterface $passwordEncoder)
    {
        $em = $this->getDoctrine()->getManager();
        $item = json_decode($request->getContent(),true);
        $user = $userRepository->findOneBy(['id' => $item['id']]);

        isset($item["email"]) && $user->setEmail($item['email']);
        isset($item["password"]) && $user->setPassword($passwordEncoder->encodePassword($user,$item["password"]));

        $em->persist($user);
        $em->flush();

        return JsonResponse::fromJsonString($this->serializeJson($user));
    }

    /**
     * @Route(path="/api/admin/user/delete", name="userDelete", methods={"DELETE"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @return Response
     * @OA\Tag(name="User")
     */
    public function userDelete(Request $request, UserRepository $userRepository)
    {
        $em = $this->getDoctrine()->getManager();
        $item = json_decode($request->getContent(),true);
        $user = $userRepository->find($item['id']);
        $response = new Response();
        if ($user){
            $em->remove($user);
            $em->flush();
            $response
                ->setContent('ok')
                ->setStatusCode(Response::HTTP_OK);
        }else{
            $response
                ->setContent('bad request')
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }
        return $response;
    }



    private function serializeJson($objet){
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getNom();
            },
        ];
        $normalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);
        $serializer = new Serializer([$normalizer], [new JsonEncoder()]);

        return $serializer->serialize($objet, 'json');
    }
}
