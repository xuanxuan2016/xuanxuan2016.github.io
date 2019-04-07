---
layout:     post
title:      "Git学习-入门"
subtitle:   "git introduction"
date:       2019-04-05 14:10
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - git
---

> 介绍使用git中一些常用命令来进行版本管理。

## 安装与配置

#### windows

##### 安装

[下载地址](https://git-scm.com/download/win)

<p>
从下载地址选择合适的版本下载，按照提示进行安装即可。
</p>

##### 用户配置

<p>
当用户提交代码时，git服务器可知道是谁进行了操作。
</p>

```linux
$ git config --global user.name "Your Name"
$ git config --global user.email "email@example.com"
```

<p>
配置文件位置
</p>

```linux
C:\Users(用户)\$USER\.gitconfig
```

##### SSH Key配置

<p>
如果使用GitHub作为代码服务器，为了能与其进行交互，需要创建SSK key并配置到GitHub上。
</p>

###### 1.创建SSH Key

```linux
#一路回车就可以
$ ssh-keygen -t rsa -C "youremail@example.com"

#如果成功在用户目录可以看到id_rsa与id_rsa.pub文件
$ ll /c/Users/beautymyth/.ssh/
total 9
-rw-r--r-- 1 beautymyth 197121 1823 三月   21 06:23 id_rsa
-rw-r--r-- 1 beautymyth 197121  398 三月   21 06:23 id_rsa.pub
```

###### 2.将Key添加到GitHub

<p>
右上角用户->设置->SSH and GPG keys->New SSH Key。将<code>id_rsa.pub</code>里的内容填写进去。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-04-05-git-study-introduction/20190407111122.png?raw=true)

#### linux

##### 安装

[版本地址](https://github.com/git/git/releases)

[下载地址](https://mirrors.edge.kernel.org/pub/software/scm/git/)

```linux
[root@iZwz9i8fd8lio2yh3oerizZ bmsource]# wget https://mirrors.edge.kernel.org/pub/software/scm/git/git-2.21.0.tar.gz
[root@iZwz9i8fd8lio2yh3oerizZ bmsource]# tar -zxf git-2.21.0.tar.gz 
[root@iZwz9i8fd8lio2yh3oerizZ bmsource]# cd git-2.21.0
[root@iZwz9i8fd8lio2yh3oerizZ git-2.21.0]# ./configure --prefix=/usr/local/git
[root@iZwz9i8fd8lio2yh3oerizZ git-2.21.0]# make && make install
```

##### 用户配置

<p>
当用户提交代码时，git服务器可知道是谁进行了操作。
</p>

```linux
$ git config --global user.name "Your Name"
$ git config --global user.email "email@example.com"
```

<p>
配置文件位置
</p>

```linux
/root/.gitconfig
```

##### SSH Key配置

<p>
如果使用GitHub作为代码服务器，为了能与其进行交互，需要创建SSK key并配置到GitHub上。
</p>

###### 1.创建SSH Key

```linux
#一路回车就可以
$ ssh-keygen -t rsa -C "youremail@example.com"

#如果成功在用户目录可以看到id_rsa与id_rsa.pub文件
[root@iZwz9i8fd8lio2yh3oerizZ /]# ll /root/.ssh
total 12
-rw------- 1 root root 1679 Apr  7 10:59 id_rsa
-rw-r--r-- 1 root root  398 Apr  7 10:59 id_rsa.pub
```

###### 2.将Key添加到GitHub

<p>
右上角用户->设置->SSH and GPG keys->New SSH Key。将<code>id_rsa.pub</code>里的内容填写进去。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-04-05-git-study-introduction/20190407111122.png?raw=true)

## 创建版本库

<p>
版本库又名仓库，英文名repository，你可以简单理解成一个目录，这个目录里面的所有文件都可以被Git管理起来，每个文件的修改、删除，Git都能跟踪，以便任何时刻都可以追踪历史，或者在将来某个时刻可以“还原”。
</p>

<p>
版本库可以从远程仓库克隆，也可以在本地创建之后再推送到远程。
</p>

#### 克隆远程仓库

```linux
#定位到本机任意目录
$ cd /d/gitstudy2_tmp/

#执行克隆命令
$ git clone git@github.com:xuanxuan2016/gitstudy2.git

#进入仓库，查看状态
$ cd gitstudy2/
$ git status

On branch master
Your branch is up to date with 'origin/master'.
```

#### 本地创建

```linux
#定位到本机任意目录
$ cd /d/gitstudy2_tmp/

#创建git目录
$ mkdir gitstudy3
$ cd gitstudy3/

#将目录变为git仓库
$ git init

#查看状态
$ git status
On branch master
```

## 本地版本库管理

<p>
在实际工作中，大部分时间都是在本地版本库里进行操作。本地版本库一般分为3个部分，工作区、暂存区、本地分支库。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-04-05-git-study-introduction/20190405162654.png?raw=true)

#### 工作区状态

<p>
查看当前工作区是否有文件修改。
</p>

```linux
$ git status

On branch master
No commits yet

Untracked files:
  (use "git add <file>..." to include in what will be committed)
        file1.txt
```

#### 文件修改比较

<p>
查看工作区文件与本地分支上的差异。
</p>

```linux
$ git diff

diff --git a/file1.txt b/file1.txt
index 9f37f61..2563660 100644
--- a/file1.txt
+++ b/file1.txt
@@ -1,3 +1,4 @@
 file1
 file11
 file111
+file1111
```

#### 文件添加

<p>
将修改的文件添加到暂存区。
</p>

```linux
#添加所有修改文件
$ git add .

On branch master
No commits yet
Changes to be committed:
  (use "git rm --cached <file>..." to unstage)
        new file:   file1.txt
```

```linux
#添加某个修改文件
$ git add file1.txt

#添加匹配的修改文件
$ git add file*
```

#### 文件提交

<p>
将暂存区文件提交到本地分支。
</p>

```linux
$ git commit -m 'new file1'

[master (root-commit) c62fa91] new file1
 1 file changed, 3 insertions(+)
 create mode 100644 file1.txt
```

#### 版本提交历史

<p>
查看版本所有的提交历史。
</p>

```linux
$ git log

commit c83cf70958f5e9b15d5a92d2b4dd12137b669ef4 (HEAD -> master)
Author: beautymyth <903628963@qq.com>
Date:   Sun Apr 7 12:07:10 2019 +0800
    change file1 1

commit c62fa9142441d4da4778ceaaaa77050b7e908a83
Author: beautymyth <903628963@qq.com>
Date:   Sun Apr 7 11:37:14 2019 +0800
    new file1
```

```linux
#单行显示
$ git log --pretty=oneline
c83cf70958f5e9b15d5a92d2b4dd12137b669ef4 (HEAD -> master) change file1 1
c62fa9142441d4da4778ceaaaa77050b7e908a83 new file1
```

#### 版本回退

##### 撤销工作区修改

<p>
撤销工作区某个文件的修改。
</p>

```linux
$ git checkout -- file1.txt
```

<p>
如果文件已添加到暂存区，可先将文件回退到工作区，再撤销修改。
</p>

```linux
#将文件从暂存区退回
$ git reset HEAD file1.txt

#撤销修改
$ git checkout -- file1.txt
```

##### 回退到历史版本

<p>
需要将工作区文件恢复到某个历史版本。
</p>

```linux
#查看版本信息，当指向为daa07a
$ git log --pretty=oneline
daa07a26f742ef888713015a99004cae8da3dae4 (HEAD -> master) change file1 2
c83cf70958f5e9b15d5a92d2b4dd12137b669ef4 change file1 1
c62fa9142441d4da4778ceaaaa77050b7e908a83 new file1

#恢复到指定版本
#字符串代表git上的版本号前面几个字符
$ git reset --hard c83cf7

#查看版本信息，已经切换到c83cf7
$ git log --pretty=oneline
c83cf70958f5e9b15d5a92d2b4dd12137b669ef4 (HEAD -> master) change file1 1
c62fa9142441d4da4778ceaaaa77050b7e908a83 new file1
```

<p>
查看版本库，历史执行命令。
</p>

```linux
$ git reflog
c83cf70 (HEAD -> master) HEAD@{0}: reset: moving to c83cf7
daa07a2 HEAD@{1}: commit: change file1 2
c83cf70 (HEAD -> master) HEAD@{2}: commit: change file1 1
c62fa91 HEAD@{3}: commit (initial): new file1
```

## 分支管理

#### 查看分支

<p>
查看本地分支信息，<code>*</code>表示当前所在分支。
</p>

```linux
$ git branch -av

  dev    c83cf70 change file1 1
* master c83cf70 change file1 1
```

#### 新建分支

```linux
$ git branch dev2
```

#### 切换分支

```linux
$ git checkout dev2
```

#### 文件贮藏

<p>
如果当前分支有文件修改，且还不能提交，这时又需要新建分支进行修改，可将当前工作区贮藏起来，用于之后的恢复。
</p>

```linux
$ git stash
Saved working directory and index state WIP on dev: 9f666fb 冲突合并
```

<p>
查看所有贮藏信息。
</p>

```linux
$ git stash list
stash@{0}: WIP on dev: 9f666fb 冲突合并
```

<p>
恢复贮藏信息。
</p>

```linux
#恢复上一个贮藏版本，自动删除贮藏信息
$ git stash pop

#恢复指定贮藏版本，不自动删除贮藏信息，需手动删除
$ git stash apply stash@{0}
```

<p>
删除贮藏信息。
</p>

```linux
$ git stash drop stash@{0}
Dropped stash@{0} (e690f81c7f33eb997476a2a24343b203993c3754)
```

#### 删除分支

```linux
$ git branch -d dev2
Deleted branch dev2 (was c83cf70).

#强制删除未合并的分支
$ git branch -D feature
```

#### 合并分支

<p>
将其他分支合并到当前分支。
</p>

```linux
$ git merge master
Updating c83cf70..e67c9f6
Fast-forward
 file1.txt | 1 +
 1 file changed, 1 insertion(+)
```

<p>
默认情况下，如果没有文件冲突，Git会使用<code>Fast forward</code>模式合并，这种模式下，如果删除分支，会丢掉分支信息。
</p>

<p>
合并分支时，加上--no-ff参数就可以用普通模式合并，合并后的历史有分支，能看出来曾经做过合并，而fast forward合并就看不出来曾经做过合并。
</p>

```linux
$ git merge --no-ff -m "merge with no-ff" dev
```

<p>
查看分支合并图。
</p>

```linux
$ git log --graph --pretty=oneline --abbrev-commit
```

#### 解决冲突

<p>
如果当前分支与需要合并的分支都对某些文件进行修改，在执行合并命令时，会提示文件冲突，需要手动解决冲突（合并文件的修改），然后再提交。
</p>

```linux
#合并分支，提示文件冲突
$ git merge master
Auto-merging file1.txt
CONFLICT (content): Merge conflict in file1.txt
Automatic merge failed; fix conflicts and then commit the result.

#查看冲突文件
$ git status

On branch dev
You have unmerged paths.
  (fix conflicts and run "git commit")
  (use "git merge --abort" to abort the merge)

Unmerged paths:
  (use "git add <file>..." to mark resolution)
        both modified:   file1.txt

#查看某个文件冲突内容
#head:代表当前分支内容
#master:代表需要合并的分支内容。
$ cat file1.txt
file1
file11
file111
file1111
file11111
<<<<<<< HEAD
file11
=======
file1111
>>>>>>> master

#手动修改文件后提交
$ git add file1.txt
$ git commit -m '冲突合并'
[dev 9f666fb] 冲突合并
```

## 远程仓库

#### 克隆仓库

<p>
默认只会获取master分支。
</p>

```linux
#克隆
$ git clone git@github.com:xuanxuan2016/gitstudy2.git

#分支
$ git branch
* master
```

#### 创建远程克隆

<p>
如果远程上是一个空的仓库，则需要创建。
</p>

```linux
1.在GitHub上创建空的仓库

2.本地仓库与远程库关联
$ git remote add origin git@github.com:xuanxuan2016/gitstudy.git

3.将本地仓库分支推送到远程
#第一次
git push -u origin master
#之后
git push origin master
```

#### 查看远程分支

```linux
$ git remote -v
origin  git@github.com:xuanxuan2016/gitstudy2.git (fetch)
origin  git@github.com:xuanxuan2016/gitstudy2.git (push)
```

#### 获取其他分支

```linux
$ git checkout --track origin/dev
Switched to a new branch 'dev'
Branch 'dev' set up to track remote branch 'dev' from 'origin'.

#或者
$ git checkout -b dev origin/dev
Switched to a new branch 'dev'
Branch 'dev' set up to track remote branch 'dev' from 'origin'.
```

#### 创建分支跟踪

<p>
操作过程中提示，提示分支未跟踪远程，则添加跟踪。
</p>

```linux
$ git branch --set-upstream-to=origin/dev dev
```

#### 推送分支

<p>
将本地修改的分支推送到远程。
</p>

```linux
$ git push origin master

Enumerating objects: 4, done.
Counting objects: 100% (4/4), done.
Delta compression using up to 4 threads
Compressing objects: 100% (2/2), done.
Writing objects: 100% (3/3), 272 bytes | 68.00 KiB/s, done.
Total 3 (delta 0), reused 0 (delta 0)
To github.com:xuanxuan2016/gitstudy2.git
   a9c0378..741bc0c  master -> master
```

#### 拉取分支

<p>
从远程分支拉取最新的内容到本地分支。
</p>

```linux
$ git pull

remote: Enumerating objects: 4, done.
remote: Counting objects: 100% (4/4), done.
remote: Compressing objects: 100% (2/2), done.
remote: Total 3 (delta 0), reused 3 (delta 0), pack-reused 0
Unpacking objects: 100% (3/3), done.
From github.com:xuanxuan2016/gitstudy2
   a9c0378..741bc0c  master     -> origin/master
Updating a9c0378..741bc0c
Fast-forward
 file1.txt | 1 +
 1 file changed, 1 insertion(+)
 create mode 100644 file1.txt
```

## 参考资料

[git官方文章](https://git-scm.com/book/zh/v2)

[git文档](https://git-scm.com/docs)

[廖雪峰教程](https://www.liaoxuefeng.com/wiki/0013739516305929606dd18361248578c67b8067c8c017b000)

[git-cheatsheet](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-04-05-git-study-introduction/git-cheatsheet.pdf)
