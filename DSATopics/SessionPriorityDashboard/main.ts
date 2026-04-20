//LETS START WITH FRONTEND NOWWWW


//custom type first of a task object that will be there
type TaskVars = 
{
  id: string; //identifier of task
  label: string; //name of task
  startMessage: string;
  doneMessage: string;
};

// Get HTML elements connection here

//listoftasks
const taskList = document.getElementById('taskList') as HTMLDivElement;

//createsessionbutton
const createSessionBtn = document.getElementById('createSessionBtn') as HTMLButtonElement;

//saveprioritybutton
const savePriorityBtn = document.getElementById('savePriorityBtn') as HTMLButtonElement;

//startprocessingbutton
const startProcessingBtn = document.getElementById('startProcessingBtn') as HTMLButtonElement;

//sessionidintext to show on frontend
const sessionIdText = document.getElementById('sessionIdText') as HTMLSpanElement;

//statusintextform like waiting, processing etc.
const statusText = document.getElementById('statusText') as HTMLSpanElement;

//dashboard main layout that we had
const dashboardLayout = document.getElementById('dashboardLayout') as HTMLDivElement;

//processing panel where tasks processing will be exhibited //which is right side 
//will start backend processing
const processingPanel = document.getElementById('processingPanel') as HTMLDivElement;


//where liveprocessing will be shown the exact container
const liveStatusArea = document.getElementById('liveStatusArea') as HTMLDivElement;

// storing currently dragged card
//use let so it can be changed
let draggedCard: HTMLDivElement | null = null;

// Store created session ID
//use let so it can be changedd
let currentSessionId: string | null = null;
//null coz at new page load, no card will have been dragged

// Update the number badges after drag-drop thing
function updateRanks(): void {
  //will return nothing just updation
  const cards = Array.from(taskList.querySelectorAll('.task-card')) as HTMLDivElement[]; //converts node list into real js array

  cards.forEach((card: HTMLDivElement, index: number) => {
    const rankElement = card.querySelector('.rank') as HTMLSpanElement;
    rankElement.textContent = String(index + 1);
  });
}

// Get current order from UI that the user has decided
function getCurrentTaskOrder(): string[] {
  const cards = Array.from(taskList.querySelectorAll('.task-card')) as HTMLDivElement[];

  return cards.map((card: HTMLDivElement) => {
    return card.dataset.id ?? '';
  });
}

// Create one live status card on right side
function addLiveStatusCard(message: string): HTMLDivElement {
  const statusItem = document.createElement('div');
  statusItem.className = 'live-status-item';
  statusItem.textContent = message;
  liveStatusArea.appendChild(statusItem);
  return statusItem;
}

// Small delay so items appear step by step
function wait(ms: number): Promise<void> {
  return new Promise((resolve: () => void) => {
    window.setTimeout(resolve, ms);
  });
}

// Setup drag and drop logic
function setupDragAndDrop(): void {
  const cards = Array.from(taskList.querySelectorAll('.task-card')) as HTMLDivElement[];

  cards.forEach((card: HTMLDivElement) => {
    card.addEventListener('dragstart', (): void => {
      draggedCard = card;
      card.classList.add('dragging');
    });

    card.addEventListener('dragend', (): void => {
      card.classList.remove('dragging');
      draggedCard = null;
      updateRanks();

      if (currentSessionId !== null) {
        savePriorityBtn.disabled = false;
      }
    });

    card.addEventListener('dragover', (event: DragEvent): void => {
      event.preventDefault();

      if (!draggedCard || draggedCard === card) {
        return;
      }

      const allCards = Array.from(taskList.querySelectorAll('.task-card')) as HTMLDivElement[];
      const draggedIndex = allCards.indexOf(draggedCard);
      const targetIndex = allCards.indexOf(card);

      if (draggedIndex < targetIndex) {
        taskList.insertBefore(draggedCard, card.nextSibling);
      } else {
        taskList.insertBefore(draggedCard, card);
      }
    });
  });
}

// Create session
// Will call PHP BACKEND to create this session
//we will use async coz we need to wait for backend response
async function createSession(): Promise<void> {
  const response = await fetch('./working/create-session.php', {
    method: 'POST'
  });
  //will call backend API file
  //fetch will send http request
  //await will help pause us until response we get
  //send post request coz creation of session will change the backend state
  const data = await response.json();
  //reads response got from backend
  currentSessionId = data.sessionId;
  sessionIdText.textContent = data.sessionId;
  statusText.textContent = 'Session Created';

  savePriorityBtn.disabled = false;
}

// Save selected priority order
async function savePriority(): Promise<void> {
  if (currentSessionId === null) {
    return;
  } //checking if session exists

  const orderedTaskIds = getCurrentTaskOrder(); //read current order

  const response = await fetch('./working/save-priority.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      sessionId: currentSessionId,
      orderedTaskIds: orderedTaskIds
    })
  }); //call php api for saving this order
  //post coz data needs to be saved

  const data = await response.json();

  statusText.textContent = data.message;
  startProcessingBtn.disabled = false;
}

// Start backend processing
async function startProcessing(): Promise<void> {
  //main execution starts here, it will update each task
  if (currentSessionId === null) {
    return;
  }

  const response = await fetch('./working/process-session.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      sessionId: currentSessionId
    })
  });

  const data = await response.json();

  if (!data.success) {
    statusText.textContent = 'Processing Failed';
    return;
  }

  statusText.textContent = 'Processing...';
  liveStatusArea.innerHTML = '';

  dashboardLayout.classList.add('processing-mode');
  processingPanel.classList.remove('hidden');

  createSessionBtn.disabled = true;
  savePriorityBtn.disabled = true;
  startProcessingBtn.disabled = true;

  const tasks = data.tasks as TaskVars[];

  for (const task of tasks) {
    const liveCard = addLiveStatusCard(task.startMessage);
    await wait(2000);
    liveCard.textContent = task.doneMessage;
    liveCard.classList.add('done');
  }

  statusText.textContent = 'Completed';
}

// Button click events
// will connect buttons with their respective function
createSessionBtn.addEventListener('click', (): void => {
  void createSession();
});

savePriorityBtn.addEventListener('click', (): void => {
  void savePriority();
});

startProcessingBtn.addEventListener('click', (): void => {
  void startProcessing();
});

// Start page setup
setupDragAndDrop(); //dragdrop activated
updateRanks(); // ranks initially correct