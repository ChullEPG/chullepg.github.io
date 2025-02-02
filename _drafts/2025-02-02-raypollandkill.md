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


Example script: 
```
script here
```


This strategy is useful in situations where you are distributing a task which can hang (stuck), crash (exit unexpectedly), or get lost (communication failure from network issue). If workers can't free themselves up, then eventually if enough tasks get stuck, all of your CPU cores may become unavailable and the script will freeze.

Ray poll and kill is a convenient way to free up stuck workers.

The conventional way to deal with stuck workers is to use ray.wait(), which is great when it works. It works by sending a gentle SIGTERM message to workers when they are deemed unresponsive. However, there are many reasons with ray.wait() may not be aggressive enough to free up the stuck worker. 

For example, if a subprocess is stuck in an infinite loop (e.g. while True), or is waiting for a blocking function to finish. This is especially true for packages written in C/C++, which interact directly with system resources and often lack built-in safeguards for interruption (i.e. not checking for termination signals). 

At this point, the cushy and polite SIGTERM command, which knocks on the door and politely waits for a response, won't cut it. SIGTERM's ruthless older cousin SIGKILL is required. SIGKILL does not knock, and does not even aim for a door. It just busts down the wall, immediately terminating the process. 


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

Context: 
The poll and kill strategy became useful for me when I distributed a task that runs [Gmsh](https://gmsh.info) under the hood. Gmsh is written in C++ and crashed out in ~5% of tasks that I was running. Initially when running the script, I noticed that over time my script would slow down, eventually grinding to a halt when all of the workers had taken up one by one.





