
function checkFordone(chunks, start, chunk, ind, tag = '') {

    if (start == chunks.length - 1 && chunk == ind) {
        postMessage({
            status: 'done',
            chunks: chunks,
            tag: tag
        });
    } else {
        postMessage({
            status: 'progress',
            start: start,
            index: ind,
            tag: tag
        });
    }


}

function processChunks1(chunks, data, tag = '') {
    let start = data.start;

    if (start >= chunks.length) {
        return chunks;
    }
    let chunk = chunks[start] ?? [];

    chunk.forEach((element, ind) => {

        data.formData.set('contacts', element);

        try {

            fetch(data.url, {
                method: 'POST',
                body: data.formData,

            })
                .then(response => {

                    return response.json(); // or response.json() if expecting JSON data
                })
                .then(response => {

                    chunks[start][ind].response = response;

                    checkFordone(chunks, start, chunk.length - 1, ind, tag);
                    // Do whatever you need to do with the response
                })
                .catch(error => {
                    checkFordone(chunks, start, chunk.length - 1, ind, tag);
                    //console.error('There was a problem with the fetch operation:', error);
                    // Handle error here
                });


        } catch (error) {

        }

        if (ind == chunk.length - 1) {
            data.start = data.start + 1;
            processChunks1(chunks, data, tag);
        }
    });
}

function chunkArray(arr, chunkSize) {
    const chunkedArrays = [];
    for (let i = 0; i < arr.length; i += chunkSize) {
        chunkedArrays.push(arr.slice(i, i + chunkSize));
    }
    return chunkedArrays;
}


function processTags(url, tags) {

    return new Promise((resolve, reject) => {

        let contacts = [];
        tags.forEach((x, ind) => {
            fetch(url + "?q=" + x, {
                method: 'get',
            })
                .then(response => {

                    return response.json(); // or response.json() if expecting JSON data
                })
                .then(response => {

                    if (tags.length - 1 == ind) {
                        if (response?.results) {
                            contacts = [...contacts, ...response.results.map(x => x.id)];
                            contacts = [... new Set(contacts)];
                            resolve(contacts);
                        } else {
                            resolve([])
                        }
                    }

                    // Do whatever you need to do with the response
                })
                .catch(error => {

                    // Handle error here
                });
        })

    });

}

self.addEventListener('message', event => {
    const inputData = event.data;
    let tags = inputData.data.formDataObject.tags;
    let share = inputData.data.formDataObject.share
    let formData = new FormData();
    Object.entries(inputData.data.formDataObject).forEach(([key, value]) => {
        formData.append(key, value);
    });
    inputData.data.formData = formData;
    if (share == 'tags') {
        tags = tags.split(',');
        processTags(inputData.data.contacts, tags).then(contacts => {
            contacts = chunkArray(contacts, 50);
            inputData.data.formData.set('tags', tags);
            processChunks1(contacts, inputData.data, tags);
        });
    } else {
        if (inputData.chunks.length == 0) {
            postMessage({
                status: 'done',
                chunks: inputData.chunks
            });
        }
        processChunks1(inputData.chunks, inputData.data);
    }


});

