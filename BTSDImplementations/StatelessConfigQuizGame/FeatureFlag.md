SO A FEATURE FLAG IDEA IS:::


IT SHOULD BE AT A QUIZ INITIALIZATION STEP

IF ITS TRUE- QUESTION ORDER WILL BE SHUFFLED AND HENCE THAT ORDER WILL GET SAVED AND QUIZ WILL INITIALIZE WITH THAT SAVED ORDER

IF ITS FALSE- QUESTION ORDER WILL BE SAME AS IN QUIZDATA.PHP FILE AND HENCE THAT WILL BE USED AS SAME 


SO THE MAIN CHANGES THAT I HAVE TO DO ARE::::

1) stop using questionId++ as the next question rule but update it
2) have to create a ordered list of quesIds for each session
3) if flag is on, shuffle that list once at session start and then initialize quiz
4) if flag is off, keep original order of the ques ids as per the quizdata file
5) move through that ordered list using an index and use that as nextquesid formula



featureFlag.json ==>>> // ACCORDING TO BSTDv3.1 -  16.6