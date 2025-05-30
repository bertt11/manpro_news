<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewsApproved;

class AdminNewsController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        
        if ($status === 'reviewed') {
            $news = News::whereIn('status', ['approved', 'rejected'])->get();
        } else {
            $news = News::where('status', 'pending')->get();
        }      
        return view('admin.news.index', compact('news'));
    }

    public function review(News $news)
    {
        $news->youtube_link = preg_replace('/.*(?:youtu\.be\/|v=|\/v\/|embed\/|watch\?v=|&v=)([^&\n?#]+)/', '$1', $news->youtube_link);
        return view('admin.news.review', compact('news'));
    }

    public function approve(News $news)
    {
        $news->status = 'approved';
        $news->save();

        // kirim email (opsional)
        // Mail::to('user@example.com')->send(new NewsApproved($news));

        return redirect()->route('admin.news.index')->with('success', 'News approved successfully!');
    }

    public function reject(Request $request, News $news)
    {
        $news->status = 'rejected';
        $news->rejection_memo = $request->input('memo', 'Ditolak tanpa alasan.');
        $news->save();

        return redirect()->route('admin.news.index')->with('error', 'News rejected with memo.');
    }

    public function takedown(News $news)
    {
        // // Change from delete to status change
        // $news->status = 'pending';  // Mengembalikan ke status pending, atau bisa diganti ke 'taken_down' jika perlu
        // $news->save();

        $news->delete(); // Atau bisa ganti status menjadi "taken_down" jika tidak ingin hapus permanen
        return back()->with('info', 'News has been taken down.');
        
        return redirect()->route('admin.news.index')->with('info', 'News has been taken down.');
    }

    public function clearMemo(News $news)
    {
        $news->rejection_memo = null;
        $news->save();
        return back()->with('success', 'Memo cleared.');
    }

    public function destroy($id)
    {
        News::where('id', $id)->delete();
        return redirect()->route('admin.news.index');
    }

    public function akses()
    {
        return view('admin.akses');
    }

}