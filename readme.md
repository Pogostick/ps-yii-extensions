PYEL - The Pogostick Yii Extension Library
==========================================

Thanks for checking out the Pogostick Yii Extension Library! We're re-branding the library as PYEL, pronounced "'''pile'''".

The first release was back in 2009 with several additional releases culminating with the award-winning v1.0.6. I've done quite
a bit of work with the library since then, and I've found many things that were in my initial design, that I thought were cool,
have become either antiquated, unnecessary, or annoying.

With the advent of [http://www.yiiframework.com/download/ Yii v1.1.6],a few of my features have been incorporated into the
code base (behavior methods and properties being accessible by the owner for example). These really need to come out of the PYEL now.

In addition, I've built an entire form framework that is poorly documented and sits alongside the original forms system I devised. This new
framework is much easier to use. So I want to get that into the library as well.

So, why the diatribe? Well, it's like this...

The original library made available, and extensive use of, runtime-defined object properties. While, again, I thought it was cool at the time;
has now become annoying and has two drawbacks I dislike. The first being that you cannot use constructs (empty() for instance) with them.
The second being the inability for IDEs to pull up phpdoc on these properties. Two bogus things IMHO.

With that said, I want to do away with the idea completely and define all properties at compile-time instead. I've already started this
work and it will be the basis of the new version 1.1.0.

I've tagged v1.0.6 so you can still get the current code for that version (which has much of the fixes mentioned above included). It will remain backward-compatible.

The new version, when released will '''NOT BE''' backward-compatible. I may change the release version because of this, we'll see.

Well, if you've read this far, thanks, you're awesome! I enjoy building tools like this and it warms my cockles to know that there are people out there
in the community that can (and do) benefit from my work. I suppose it's my little karma contribution.

So, without further ado, here's some really short and probably confusing documentation.


Installation
------------

Requirements
------------

* PHP v5.3+
 The PYEL requires PHP v5.3+. While it will work somewhat with version 5.2x, some functionality maybe hosed and I have no idea what. So use at your own risk.

* Yii v1.1+
 The PYEL extends the Yii Framework and, of course (duh!), requires it to operate properly.
