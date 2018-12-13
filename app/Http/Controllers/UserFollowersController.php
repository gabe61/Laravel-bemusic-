<?php namespace App\Http\Controllers;

use Auth;
use App\User;
use Illuminate\Http\Request;
use Common\Core\Controller;

class UserFollowersController extends Controller {

    /**
     * @var User
     */
    private $model;

    /**
     * @var Request
     */
    private $request;

    /**
     * UsersController constructor.
     *
     * @param User $user
     * @param Request $request
     */
    public function __construct(User $user, Request $request)
    {
        $this->model = $user;
        $this->request = $request;

        $this->middleware('auth');
    }

    /**
     * Follow user with given id.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function follow($id)
    {
        return $id;
        $user = $this->model->findOrFail($id);

        if ($user->id !== Auth::user()->id) {
            Auth::user()->followedUsers()->sync([$id], false);
        }

        return $this->success();
    }

    /**
     * UnFollow user with given id.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function unfollow($id)
    {
        $user = $this->model->findOrFail($id);

        if ($user->id != Auth::user()->id) {
            Auth::user()->followedUsers()->detach($id);
        }

        return $this->success();
    }
}
