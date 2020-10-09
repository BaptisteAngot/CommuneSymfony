<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class SecurityController extends AbstractController
{
    /**
     * @Route(name="api_login", path="/api/login_check",methods={"POST"})
     * @OA\Response(
     *     response="200",
     *     description="Login by API",
     *     @OA\JsonContent(
     *      type="json",
     *     example="{'token' : 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2MDIyNTg1MTEsImV4cCI6MTYwMjI2MjExMSwicm9sZXMiOlsiUk9MRV9BRE1JTiIsIlJPTEVfVVNFUiJdLCJ1c2VybmFtZSI6ImFkbWluQGFkbWluLmZyIn0.oPj2FHZDgkiWc5J7uiyWMiuTm1ICFh11SBfGoYaLe1tHjJOK7vWUGentLdSITtzNGGRtVqiauAOliQTSoYp5qN7dVYjpngcjS0XTJk4n68hc-BdhPmFsV0e5SPuRhebeeil1FQtBjROoUleq-K4xLeXDLI3Uv-38Rtu0rpU5Jx-NVk_Ofrn8zlWnBLcCxj0TEepSsda3mcS__HJeu-CB8mclNUUmJrjHn-CKOxWS4-BUNsYOen8nJOqP33UcHaTwIvqqMeLwnxki6gmvsNukupGzD83nEBzzw3HyyRu4iBsWQc8RWTn0JxWo5zhlV1cxAud3mG1eEep47F8ODEgzK4IhjRINmPKjMFt9u1ZUugTitGzBlXYwPzydsaqbk1M6FwunF9aohUwEp8vb5Vf4rK3fbVUAkJUEBHJM7Y0Rf_6vR1fcUIjS93XAf0IHwz8z-3I2JdOLOwv0cebjpct-lB9xxFtNi566pDaLLW_b615pw6xOCMaMRh-wrDjtI-Vv2QELyyPCj_41nOvSEuCUeec656x5oM9lWDg1bsfIz15AZS16WQrILWnkdPKKqQbehYsBdbwoE74RxzhJBx_PvgFeLGfGpLp0CrHurLm5IM99JZd96SecgHk4UdD7AIrvgc-GKkprr53Fb5c-cG5yMhb65rzEotDqxB0qLGYf6Z8'}"
     *     )
     * )
     * @OA\Response(
     *     response="401",
     *     description="Invalid credentials",
     *     @OA\JsonContent(
     *      type="json",
     *     example="{
          'code': 401,
          'message': 'Invalid credentials'
}"
     *     )
     * )
     * @OA\Parameter(
     *     name="username",
     *     in="query",
     *     required=true,
     *     description="Your mail",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="password",
     *     in="query",
     *     required=true,
     *     description="Your password",
     *     @OA\Schema(type="string")
     * )
     * @return JsonResponse
     * @OA\Tag(name="User")
     */
    public function api_login(): JsonResponse
    {
        $user = $this->getUser();

        return new JsonResponse([
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ]);
    }

    /**
     * @Route("/logout", name="app_logout", methods={"GET"})
     */
    public function logout()
    {
        // controller can be blank: it will never be executed!
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }
}
