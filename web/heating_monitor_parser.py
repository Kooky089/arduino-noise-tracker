
# Copyright (c) 2019, Volker Jacht
# License: BSD 2-Clause License
# Project: https://github.com/Kooky089/

import csv
import datetime
import bisect

class interval:
	def __init__(self, begin, end):
		self.begin = begin
		self.end = end
		
	def __lt__(self, other):
		return self.end < other
		
	def __gt__(self, other):
		return self.begin > other
		
	def crop(self, left, right):
		if self.begin < left:
			self.begin = left
		if self.end > right:
			self.end = right
	
	def duration(self):
		return self.end - self.begin
		

def get_interval(list, time_begin, time_end):
	first = bisect.bisect_left(list, time_begin, lo=0, hi=len(list))
	last = bisect.bisect_right(list, time_end, lo=first, hi=len(list))
	
	if first == last:
		return []
	
	list[first].crop(time_begin, time_end)
	list[last-1].crop(time_begin, time_end)
	
	return list[first:last]

def get_interval_day(list, date):
	return get_interval(list, date, date + datetime.timedelta(days=1))
	
def sum_interval(list):
	sum = datetime.timedelta()
	for interval in list:
		sum += interval.duration()
	return sum
	

def dateTimeToString(date):
#	result = "(" + str(date.year) + ", " + str(date.month-1) + ", "+  str(date.day) + ", "
	result = "(0,0,0,"
	result += str(date.hour) + ", " + str(date.minute) + ", " +  str(date.second) + ")"
	return result
	
def dateTimeToDateString(date):
	result = str(date.year) + "-" + str(date.month) + "-"+  str(date.day)
	return result
	
def dateToText(date):
	return date.strftime("%a %Y-%d-%m")
	
def duration_to_consumption(duration):
	return 1.92*duration.seconds/3600.0/0.845
	
with open('noise_tracker.csv') as csvfile:
	readCSV = csv.reader(csvfile, delimiter=';')

	list_tmp = []
	last_element = "0"
	for row in readCSV:
		if row[1] != last_element:
			last_element = row[1]
			list_tmp.append(row)

	list = []
	for i in range(len(list_tmp)):
		if list_tmp[i][1] == "1" and i < len(list_tmp)-1:
			list.append(interval(datetime.datetime.strptime(list_tmp[i][0], "%Y-%m-%dT%H:%M:%S"),datetime.datetime.strptime(list_tmp[i+1][0], "%Y-%m-%dT%H:%M:%S")))
	
	current_state = list_tmp[len(list_tmp)-1][1]
	del list_tmp
	
	first = list[0].begin
	last = list[len(list)-1].end
	
	first = datetime.datetime(first.year, first.month, first.day)
	last = datetime.datetime(last.year, last.month, last.day)
	
	# Calendar Chart
	comma = ""
	output = ""
	for i in range((last-first).days+1):
		current_day = first + datetime.timedelta(days=1*i)
		current_sum = sum_interval(get_interval_day(list, current_day))
		output += comma + "[new Date(" + str(current_day.year) + ", " + str(current_day.month-1) + ", "+  str(current_day.day) + "), " + str("%.2f"%duration_to_consumption(current_sum)) + "]"
		comma = ","
	print(output)
	
	# Timeline Chart
	comma = ""
	output = ""
	for i in range((last-first).days+1):
		current_day = last - datetime.timedelta(days=1*i)
		interval = get_interval_day(list, current_day)
		for event in interval:
			output += comma + "['" + dateToText(current_day) + "', new Date" + dateTimeToString(event.begin) + ", new Date" + dateTimeToString(event.end) + "]"
			comma = ","
	output += comma + "['" + dateToText(datetime.datetime.now()) + "', new Date" + dateTimeToString(datetime.datetime.now()) + ", new Date" + dateTimeToString(datetime.datetime.now()) + "]"
	print(output)
	
	# Gauge Chart
	print(str("%.4f"%(4742.66+15.72-duration_to_consumption(sum_interval(list)))))
	# 14.09.19: 72,5 * 221 * 296
	if current_state == "1":
		print("#34A853")
	else:
		print("#EEEEEE")