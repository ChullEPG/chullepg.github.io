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


Example script (Motivation and explanation below): 
```
import ray
import time 
import multiprocessing


# Polling timeout 
TIMEOUT = 100 


num_cpus = multiprocessing.cpu_count() //2 
ray.init(num_cpus = num_cpus, ignore_reinit_error = True)


# Removal is O(1) in a set in and up to O(n) in a list
# But ray.get() expects a list
# And unless you have thousands of CPU cores
# This won't meaningfully affect performance 
not_done = []


while 


    done, not_done = ray.wait(task_refs, num_returns = 1, timeout = 1.0)

    for task_ref in done:
        ray.get(task_ref)
        not_done.remove(task_ref)

    # Poll unfinished tasks
    for task_ref in not_done:
        if elapsed_time > TIMEOUT: 
            to_cancel.append(task_ref)


    for task_ref in to_cancel:
        print(f"Cancelling task {f}") 
        ray.cancel(task_ref)
        futures.remove(task_ref)

# Ray should automatically shut down when your script ends
# But to be sure... 
ray.shutdown()
```

Motivation: 

The poll and kill strategy is useful in situations where you are using ray to distribute a task which can hang (get stuck), crash (exit unexpectedly), or get lost (communication failure from network issue). If workers can't free themselves up, then your script will slow down and may eventually freeze as all workers get stuck. No bueno. 

The conventional way to deal with unresponsive workers in ray is to use ray.wait(), which sends a gentle SIGTERM message to workers when they are deemed unresponsive. This command is great when it works, however, there are many reasons why SIGTERM may not be aggressive enough to free up a stuck worker. 

SIGTERM is polite -- it knocks on the door of a process and waits for it to acknowledge SIGTERMs presence. However, sometimes this means it is too polite. if a subprocess is stuck in an infinite loop (e.g. while True), or is waiting for a blocking function to finish, then it won't stop to check for SIGTERM. This problem is especially salient when you are running a package written in C/C++, which interacts directly with system resources. You will find these packages often lack built-in safeguards for interruption, i.e. they don't stop to check for termination signals. 

In these cases, SIGTERM's ruthless older cousin SIGKILL is required. SIGKILL does not knock, does not even aim for a door, and takes no prisoners. It busts through the wall, immediately terminating the process. 

This brings us to ray.cancel()



You can check if your system resources are being taken up using the ```top``` or ```htop``` commands. 



The only catch is you need some idea of how long your task should take in the first place
[[[ ask ChatGPT for examples where this isn't the case]]]


Imports
```
import ray 
import time
```

```

ray.cancel(force = True) #recursive = True by default
```

Context: 
The poll and kill strategy became useful for me when I distributed a task that runs [Gmsh](https://gmsh.info) under the hood. Gmsh is written in C++ and crashed out in ~5% of tasks that I was running. Initially when running the script, I noticed that over time my script would slow down, eventually grinding to a halt when all of the workers had taken up one by one.





