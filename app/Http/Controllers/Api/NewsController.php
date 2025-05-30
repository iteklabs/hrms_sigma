<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\News\IndexRequest;
use App\Http\Requests\Api\News\StoreRequest;
use App\Http\Requests\Api\News\UpdateRequest;
use App\Http\Requests\Api\News\DeleteRequest;
use Examyou\RestAPI\Exceptions\ApiException;
use App\Models\News;
use App\Models\NewsUser;

class NewsController extends ApiBaseController
{
	protected $model = News::class;

	protected $indexRequest = IndexRequest::class;
	protected $storeRequest = StoreRequest::class;
	protected $updateRequest = UpdateRequest::class;
	protected $deleteRequest = DeleteRequest::class;

	public function storing(News $news)
	{
		$request = request();

		if ($request->status == 'published') {
			$news->status = $request->status;
		} else {
			$news->status = $request->status;
		}

		return $news;
	}

	public function updating(News $news)
	{
		$request = request();

		$selectedNews = News::where('id', $news->id)->select('news.status');
		$selectedNews = $selectedNews->first();

		if ($selectedNews->status == 'published' && $request->status == 'draft') {
			throw new ApiException("The news is already published, you can not draft it ");
		} else {
			$news->title = $request->title;
			$news->description = $request->description;
		}

		NewsUser::where('news_id', $news->id)->delete();

		return $news;
	}


	public function stored(News $news)
	{
		$this->addAndUpdateNews($news);
	}


	public function updated(News $news)
	{

		$this->addAndUpdateNews($news);
	}

	public function addAndUpdateNews(News $news)
	{
		$request = request();

		if (isset($request->user_id)) {


			foreach ($request->user_id as $userId) {

				$id = $this->getIdFromHash($userId);

				$newsUser = new NewsUser();
				$newsUser->news_id = $news->id;
				$newsUser->user_id = $id;
				$newsUser->save();
			}
		}

		return $news;
	}

	public function publishNews()
	{
		$request = request();
		$id = $this->getIdFromHash($request->id);
		$news = News::find($id);
		$news->status = 'published';
		$news->save();
	}
}