#!/bin/bash
git checkout master ; git pull
git checkout dev ; git pull 
git checkout master ; rm -Rf ../tmp/*
git branch