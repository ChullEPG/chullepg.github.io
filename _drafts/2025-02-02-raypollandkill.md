---
layout: post
title: Ray Poll and Kill
date: 2025-05-01 11:12:00-0000
description: How to kill individual processes with ray
tags: learning
categories: programming
related_posts: false
---

Ray poll and kill allows a user to detect and terminate unresponsive tasks processes in a distributed environment without cancelling the entire script altogether.

```
script here
```


This strategy is useful in situations where you are distributing a task which can hang (stuck), crash (exit unexpectedly), or get lost (communication failure from network issue). If workers can't free themselves up, then eventually if enough tasks get stuck, all of your CPU cores may become unavailable and the script will freeze.

Ray poll and kill is a convenient way to free up stuck workers.

The conventional way to deal with stuck workers is to use ray.wait(), which is great when it works. It works by sending a gentle SIGTERM message to workers when they are deemed unresponsive. However, there are many reasons with ray.wait() may not be aggressive enough to free up the stuck worker. 

For example, if a subprocess is stuck in an infinite loop (e.g. while True), or is waiting on a blocking I/O Operation 



Concrete example: For example, you might be generating a large dataset of FEM solves via simulation. Such complex tasks sometimes rely on software that is written in C or C++ and does not respond to kind SIGTERM 

The only catch is you need some idea of how long your task should take in the first place
[[[ ask ChatGPT for examples where this isn't the case]]]



Imports
```
import ray 
import multiprocessing
```

```

ray.cancel(force = True) #recursive = True by default
```








Entire script
```
import ray 
import multiprocessing
```